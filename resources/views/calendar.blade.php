<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel Calendar Application</title>
    
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/index.global.min.css" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/index.global.min.js"></script>
    
    <!-- Bootstrap for Modals and styling -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }
        h1 {
            text-align: center;
            color: #4a90e2;
            padding-top: 20px;
        }
        #calendar {
            width: 100%;
            height: 80vh;
            margin: 0;
            padding: 0;
        }
        .modal-header {
            background-color: #4a90e2;
            color: white;
        }

        /* Tooltip styles */
        .tooltip {
            font-size: 14px;
            color: #333;
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            position: absolute;
            z-index: 1000;
            max-width: 200px;
            word-wrap: break-word;
            pointer-events: none;
            transition: opacity 0.2s ease-in-out;
            opacity: 0;
        }
        .tooltip.visible {
            opacity: 1;
        }
    </style>
</head>
<body>

    <h1>Interactive Laravel Calendar</h1>
    <div id="calendar"></div>

    <!-- Modal for event creation and editing -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="eventForm">
                        <div class="mb-3">
                            <label for="title" class="form-label">Event Title</label>
                            <input type="text" class="form-control" id="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="datetime-local" class="form-control" id="startDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="datetime-local" class="form-control" id="endDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Event</button>
                    </form>
                    <button id="deleteEvent" class="btn btn-danger mt-3" style="display: none;">Delete Event</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    var tooltip = null; // Define the tooltip variable
    var token = '6|SGC5zty5QvBdulgLPfacLE06nCXdGdOYqXPp8L4C'; // Set your token here
    var isDragging = false; // Flag to check if dragging is happening

    // Function to remove tooltip
    function removeTooltip() {
        if (tooltip) {
            tooltip.remove();
            tooltip = null;
        }
    }

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        selectable: true,
        editable: true,
        events: function(info, successCallback, failureCallback) {
            fetch('/api/events', {
                method: 'GET',
                headers: { 
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
            })
            .then(response => response.json())
            .then(data => successCallback(data))
            .catch(error => failureCallback(error));
        },
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },

        // Prevent tooltip during drag operations
        eventMouseEnter: function (info) {
            // Only show tooltip if not dragging and not an ongoing drag operation
            if (!isDragging) {
                removeTooltip(); // Remove any existing tooltip first

                tooltip = document.createElement('div');
                tooltip.classList.add('tooltip');
                tooltip.innerHTML = 
                    `Title: ${info.event.title}<br>` +
                    `Description: ${info.event.extendedProps.description || 'No description'}<br>` +
                    `Start: ${info.event.start.toISOString().split('T')[0]} ${info.event.start.toISOString().split('T')[1].substring(0, 5)}<br>` +
                    `End: ${info.event.end.toISOString().split('T')[0]} ${info.event.end.toISOString().split('T')[1].substring(0, 5)}`;
                
                document.body.appendChild(tooltip);
                tooltip.style.left = `${info.jsEvent.pageX + 10}px`;
                tooltip.style.top = `${info.jsEvent.pageY + 10}px`;

                setTimeout(() => {
                    if (tooltip) {
                        tooltip.classList.add('visible');
                    }
                }, 50);
            }
        },

        // Hide and remove tooltip when mouse leaves
        eventMouseLeave: function () {
            if (tooltip) {
                tooltip.classList.remove('visible');
                setTimeout(removeTooltip, 200);
            }
        },

        // Explicitly manage dragging state
        eventDragStart: function() {
            isDragging = true;
            removeTooltip(); // Ensure tooltip is removed
        },

        eventDragStop: function() {
            isDragging = false;
            removeTooltip(); // Extra safety to remove any lingering tooltip
        },

        // Prevent tooltip during drag/drop
        eventDrop: function (info) {
            isDragging = false;
            removeTooltip();

            fetch(`/api/events/${info.event.id}`, {
                method: 'PUT',
                headers: { 
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`,
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    title: info.event.title,
                    start: info.event.startStr,
                    end: info.event.endStr,
                    description: info.event.extendedProps.description
                }),
            })
            .then(response => response.json())
            .then(data => {
                calendar.refetchEvents();
            })
            .catch(error => console.error('Error:', error));
        },

        // Rest of the existing event handling code remains the same...
        select: function (info) {
            $('#eventForm')[0].reset();
            $('#deleteEvent').hide();
            $('#eventModal').modal('show');
            $('#startDate').val(info.startStr);
            $('#endDate').val(info.endStr);

            $('#eventForm').off('submit').on('submit', function (e) {
                e.preventDefault();
                var title = $('#title').val();
                var start = $('#startDate').val();
                var end = $('#endDate').val();
                var description = $('#description').val();

                if (title && start && end) {
                    fetch('/api/events', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${token}`,
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ title, start, end, description })
                    })
                    .then(response => response.json())
                    .then(data => {
                        calendar.refetchEvents();
                        $('#eventModal').modal('hide');
                    })
                    .catch(error => console.error('Error:', error));
                }
            });
        },

        eventClick: function (info) {
            $('#title').val(info.event.title);
            $('#startDate').val(info.event.start.toISOString().split('T')[0] + 'T' + info.event.start.toISOString().split('T')[1].substring(0, 5));
            $('#endDate').val(info.event.end.toISOString().split('T')[0] + 'T' + info.event.end.toISOString().split('T')[1].substring(0, 5));
            $('#description').val(info.event.extendedProps.description || '');

            $('#eventModal').modal('show');
            $('#deleteEvent').show();

            $('#eventForm').off('submit').on('submit', function (e) {
                e.preventDefault();
                var newTitle = $('#title').val();
                var newStart = $('#startDate').val();
                var newEnd = $('#endDate').val();
                var newDescription = $('#description').val();

                fetch(`/api/events/${info.event.id}`, {
                    method: 'PUT',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ title: newTitle, start: newStart, end: newEnd, description: newDescription })
                })
                .then(response => response.json())
                .then(data => {
                    info.event.setProp('title', newTitle);
                    info.event.setStart(newStart);
                    info.event.setEnd(newEnd);
                    info.event.setExtendedProp('description', newDescription);
                    $('#eventModal').modal('hide');
                })
                .catch(error => console.error('Error:', error));
            });

            $('#deleteEvent').off('click').on('click', function () {
                if (confirm('Are you sure you want to delete this event?')) {
                    fetch(`/api/events/${info.event.id}`, {
                        method: 'DELETE',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Authorization': `Bearer ${token}`,
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        info.event.remove();
                        $('#eventModal').modal('hide');
                    })
                    .catch(error => console.error('Error:', error));
                }
            });
        },
    });

    calendar.render();
});
    </script>
</body>
</html>
