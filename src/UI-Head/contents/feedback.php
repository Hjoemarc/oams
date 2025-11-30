<section>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0"><i class="fa fa-comments text-primary me-2"></i>Parent Feedback</h4>
            <small class="text-muted">Share your thoughts, suggestions, and concerns with the school</small>
        </div>
        <button class="btn btn-success btn-sm" id="newFeedbackBtn">
            <i class="fa fa-plus"></i> Submit New Feedback
        </button>
    </div>

    <!-- Feedback Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <i class="fa fa-comments"></i>
                <h5 id="totalFeedback">8</h5>
                <small>Total Feedback</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <i class="fa fa-clock"></i>
                <h5 id="pendingFeedback">2</h5>
                <small>Pending Review</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <i class="fa fa-check-circle"></i>
                <h5 id="reviewedFeedback">5</h5>
                <small>Reviewed</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <i class="fa fa-reply"></i>
                <h5 id="respondedFeedback">1</h5>
                <small>Responded</small>
            </div>
        </div>
    </div>

    <!-- Feedback Filters -->
    <div class="card mb-3" style="box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Filter by Category:</label>
                    <select class="form-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="Academic">Academic</option>
                        <option value="Facilities">Facilities</option>
                        <option value="Transportation">Transportation</option>
                        <option value="Health & Safety">Health & Safety</option>
                        <option value="Events">Events</option>
                        <option value="Teachers">Teachers</option>
                        <option value="Administration">Administration</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Status:</label>
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="Pending">Pending</option>
                        <option value="Reviewed">Reviewed</option>
                        <option value="Responded">Responded</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by Priority:</label>
                    <select class="form-select" id="priorityFilter">
                        <option value="">All Priorities</option>
                        <option value="Low">Low</option>
                        <option value="Medium">Medium</option>
                        <option value="High">High</option>
                        <option value="Urgent">Urgent</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-secondary w-100" id="clearFilters">Clear Filters</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback List -->
    <div class="card" style="box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);">
        <div class="card-header" style="background-color: #f8f9fa; border-bottom: 1px solid #ddd;">
            <h6 class="mb-0" style="color: #555;">My Feedback History</h6>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover mb-0" id="feedback-tbl">
                <thead class="table-light text-dark">
                    <tr>
                        <th class="text-center" style="width:5%">#</th>
                        <th>Date Submitted</th>
                        <th>Category</th>
                        <th>Subject</th>
                        <th>Priority</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="feedback-data-body"></tbody>
            </table>
        </div>
    </div>

    <!-- Submit Feedback Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="feedbackForm">
                    <div class="modal-header" style="background-color: #f8f9fa; border-bottom: 1px solid #ddd;">
                        <h5 class="modal-title" style="color: #555;">Submit Feedback</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="feedbackId">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Category *</label>
                                <select class="form-control" id="feedbackCategory" required>
                                    <option value="">Select Category</option>
                                    <option value="Academic">Academic Performance</option>
                                    <option value="Facilities">School Facilities</option>
                                    <option value="Transportation">Transportation</option>
                                    <option value="Health & Safety">Health & Safety</option>
                                    <option value="Events">School Events</option>
                                    <option value="Teachers">Teachers/Staff</option>
                                    <option value="Administration">Administration</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Priority Level *</label>
                                <select class="form-control" id="feedbackPriority" required>
                                    <option value="">Select Priority</option>
                                    <option value="Low">Low - General suggestion</option>
                                    <option value="Medium">Medium - Important feedback</option>
                                    <option value="High">High - Needs attention</option>
                                    <option value="Urgent">Urgent - Immediate action needed</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Related to Child (Optional)</label>
                            <select class="form-control" id="relatedChild">
                                <option value="">General feedback (not child-specific)</option>
                                <!-- Will be populated by JavaScript -->
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subject/Title *</label>
                            <input type="text" class="form-control" id="feedbackSubject"
                                placeholder="Brief summary of your feedback" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Detailed Feedback *</label>
                            <textarea class="form-control" id="feedbackMessage" rows="6"
                                placeholder="Please provide detailed information about your feedback, concerns, or suggestions..."
                                required></textarea>
                            <small class="text-muted">Minimum 20 characters required</small>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Preferred Contact Method</label>
                                <select class="form-control" id="contactMethod">
                                    <option value="Email">Email</option>
                                    <option value="Phone">Phone Call</option>
                                    <option value="SMS">SMS/Text Message</option>
                                    <option value="In-Person">In-Person Meeting</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Anonymous Feedback</label>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" id="anonymousFeedback">
                                    <label class="form-check-label" for="anonymousFeedback">
                                        Submit this feedback anonymously
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-light"
                            style="border: 1px solid #d1ecf1; background-color: #e8f4fd; color: #0c5460;">
                            <i class="fa fa-info-circle me-2"></i>
                            <strong>Note:</strong> Your feedback is important to us. We aim to review all feedback
                            within 3-5 business days and will respond as appropriate.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success text-white" type="submit">
                            <i class="fa fa-paper-plane me-1"></i>Submit Feedback
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Feedback Modal -->
    <div class="modal fade" id="viewFeedbackModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background-color: #f8f9fa; border-bottom: 1px solid #ddd;">
                    <h5 class="modal-title" style="color: #555;">Feedback Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="feedbackDetailsContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" id="editFeedbackBtn">
                        <i class="fa fa-edit me-1"></i>Edit Feedback
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Sample feedback data
    const feedbackData = [
        {
            id: 1,
            dateSubmitted: "2024-11-20",
            category: "Academic",
            subject: "Request for additional Math tutoring",
            priority: "Medium",
            status: "Responded",
            message: "My child is struggling with fractions. Could the school provide additional tutoring sessions?",
            relatedChild: "Maria Santos",
            contactMethod: "Email",
            anonymous: false,
            response: "Thank you for your feedback. We have arranged additional Math tutoring sessions on Wednesdays and Fridays. Please contact Ms. Rodriguez for enrollment.",
            responseDate: "2024-11-22",
            respondedBy: "Academic Coordinator"
        },
        {
            id: 2,
            dateSubmitted: "2024-11-18",
            category: "Facilities",
            subject: "Playground equipment needs maintenance",
            priority: "High",
            status: "Reviewed",
            message: "The swing set in the playground has a broken chain that could be dangerous for children.",
            relatedChild: "",
            contactMethod: "Phone",
            anonymous: false,
            response: "",
            responseDate: "",
            respondedBy: ""
        },
        {
            id: 3,
            dateSubmitted: "2024-11-15",
            category: "Transportation",
            subject: "School bus route timing",
            priority: "Low",
            status: "Pending",
            message: "The school bus arrives 10 minutes early, making it difficult for children to catch it on time.",
            relatedChild: "Juan Santos",
            contactMethod: "SMS",
            anonymous: false,
            response: "",
            responseDate: "",
            respondedBy: ""
        },
        {
            id: 4,
            dateSubmitted: "2024-11-10",
            category: "Events",
            subject: "Science Fair participation",
            priority: "Low",
            status: "Responded",
            message: "Great job organizing the Science Fair! My child really enjoyed participating and learned a lot.",
            relatedChild: "Maria Santos",
            contactMethod: "Email",
            anonymous: false,
            response: "Thank you for your positive feedback! We're glad Maria enjoyed the Science Fair. We look forward to more events like this.",
            responseDate: "2024-11-12",
            respondedBy: "Event Coordinator"
        },
        {
            id: 5,
            dateSubmitted: "2024-11-05",
            category: "Teachers",
            subject: "Excellent teaching by Ms. Garcia",
            priority: "Low",
            status: "Reviewed",
            message: "I want to commend Ms. Garcia for her excellent teaching methods. My child has shown great improvement in English.",
            relatedChild: "Juan Santos",
            contactMethod: "In-Person",
            anonymous: false,
            response: "",
            responseDate: "",
            respondedBy: ""
        }
    ];

    let filteredFeedback = [...feedbackData];
    const childrenOptions = ["Maria Santos", "Juan Santos"]; // This would be populated from actual data

    function renderFeedbackTable() {
        const tbody = $('#feedback-data-body');
        tbody.html('');
        let i = 1;

        filteredFeedback.forEach(feedback => {
            const statusBadge = getStatusBadge(feedback.status);
            const priorityBadge = getPriorityBadge(feedback.priority);
            const categoryBadge = `<span class="category-badge">${feedback.category}</span>`;
            const actions = getActionButtons(feedback);

            const tr = $('<tr></tr>');
            tr.html(`
                <td class="text-center">${i++}</td>
                <td style="color: #666;">${formatDate(feedback.dateSubmitted)}</td>
                <td>${categoryBadge}</td>
                <td style="color: #555;">${feedback.subject}</td>
                <td>${priorityBadge}</td>
                <td class="text-center">${statusBadge}</td>
                <td class="text-center">${actions}</td>
            `);
            tbody.append(tr);
        });

        updateStatsCards();
    }

    function getStatusBadge(status) {
        const badges = {
            'Pending': '<span class="status-badge status-pending">Pending</span>',
            'Reviewed': '<span class="status-badge status-reviewed">Reviewed</span>',
            'Responded': '<span class="status-badge status-responded">Responded</span>'
        };
        return badges[status] || '<span class="status-badge">Unknown</span>';
    }

    function getPriorityBadge(priority) {
        const badges = {
            'Low': '<span class="priority-badge priority-low">Low</span>',
            'Medium': '<span class="priority-badge priority-medium">Medium</span>',
            'High': '<span class="priority-badge priority-high">High</span>',
            'Urgent': '<span class="priority-badge priority-urgent">Urgent</span>'
        };
        return badges[priority] || '<span class="priority-badge">Unknown</span>';
    }

    function getActionButtons(feedback) {
        return `
            <button class="btn-action view" onclick="viewFeedback(${feedback.id})" title="View Details">
                <i class="fa fa-eye"></i>
            </button>
            ${feedback.status === 'Pending' ?
                `<button class="btn-action edit" onclick="editFeedback(${feedback.id})" title="Edit">
                    <i class="fa fa-edit"></i>
                </button>
                <button class="btn-action delete" onclick="deleteFeedback(${feedback.id})" title="Delete">
                    <i class="fa fa-trash"></i>
                </button>` : ''
            }
        `;
    }

    function updateStatsCards() {
        const total = feedbackData.length;
        const pending = feedbackData.filter(f => f.status === 'Pending').length;
        const reviewed = feedbackData.filter(f => f.status === 'Reviewed').length;
        const responded = feedbackData.filter(f => f.status === 'Responded').length;

        $('#totalFeedback').text(total);
        $('#pendingFeedback').text(pending);
        $('#reviewedFeedback').text(reviewed);
        $('#respondedFeedback').text(responded);
    }

    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    function populateChildrenOptions() {
        const select = $('#relatedChild');
        select.html('<option value="">General feedback (not child-specific)</option>');
        childrenOptions.forEach(child => {
            select.append(`<option value="${child}">${child}</option>`);
        });
    }

    function viewFeedback(id) {
        const feedback = feedbackData.find(f => f.id === id);
        if (feedback) {
            const content = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Date Submitted:</strong><br>
                        <span style="color: #666;">${formatDate(feedback.dateSubmitted)}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong><br>
                        ${getStatusBadge(feedback.status)}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Category:</strong><br>
                        <span class="category-badge">${feedback.category}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Priority:</strong><br>
                        ${getPriorityBadge(feedback.priority)}
                    </div>
                </div>
                ${feedback.relatedChild ? `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Related to Child:</strong><br>
                        <span style="color: #666;">${feedback.relatedChild}</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Contact Method:</strong><br>
                        <span style="color: #666;">${feedback.contactMethod}</span>
                    </div>
                </div>
                ` : ''}
                <div class="mb-3">
                    <strong>Subject:</strong><br>
                    <span style="color: #555;">${feedback.subject}</span>
                </div>
                <div class="mb-3">
                    <strong>Your Feedback:</strong><br>
                    <div class="border p-3 rounded" style="background-color: #f8f9fa; color: #555;">
                        ${feedback.message}
                    </div>
                </div>
                ${feedback.response ? `
                <div class="mb-3">
                    <strong>School Response:</strong><br>
                    <div class="border p-3 rounded" style="background-color: #e8f5e8; color: #2d5a2d;">
                        ${feedback.response}
                        <hr>
                        <small class="text-muted">
                            Responded on ${formatDate(feedback.responseDate)} by ${feedback.respondedBy}
                        </small>
                    </div>
                </div>
                ` : ''}
            `;
            $('#feedbackDetailsContent').html(content);
            $('#editFeedbackBtn').data('feedback-id', id);
            $('#viewFeedbackModal').modal('show');
        }
    }

    function editFeedback(id) {
        const feedback = feedbackData.find(f => f.id === id);
        if (feedback) {
            $('#feedbackId').val(feedback.id);
            $('#feedbackCategory').val(feedback.category);
            $('#feedbackPriority').val(feedback.priority);
            $('#relatedChild').val(feedback.relatedChild);
            $('#feedbackSubject').val(feedback.subject);
            $('#feedbackMessage').val(feedback.message);
            $('#contactMethod').val(feedback.contactMethod);
            $('#anonymousFeedback').prop('checked', feedback.anonymous);
            $('#feedbackModal').modal('show');
        }
    }

    function deleteFeedback(id) {
        if (confirm('Are you sure you want to delete this feedback? This action cannot be undone.')) {
            const index = feedbackData.findIndex(f => f.id === id);
            if (index > -1) {
                feedbackData.splice(index, 1);
                applyFilters();
                showNotification('Feedback deleted successfully', 'info');
            }
        }
    }

    function applyFilters() {
        filteredFeedback = feedbackData.filter(feedback => {
            const categoryMatch = !$('#categoryFilter').val() || feedback.category === $('#categoryFilter').val();
            const statusMatch = !$('#statusFilter').val() || feedback.status === $('#statusFilter').val();
            const priorityMatch = !$('#priorityFilter').val() || feedback.priority === $('#priorityFilter').val();

            return categoryMatch && statusMatch && priorityMatch;
        });
        renderFeedbackTable();
    }

    function showNotification(message, type = 'success') {
        // You can implement a toast notification system here
        alert(message);
    }

    // Event Handlers
    $(document).ready(function () {
        renderFeedbackTable();
        populateChildrenOptions();

        $('#newFeedbackBtn').click(function () {
            $('#feedbackForm')[0].reset();
            $('#feedbackId').val('');
            $('#feedbackModal').modal('show');
        });

        $('#feedbackForm').submit(function (e) {
            e.preventDefault();

            const message = $('#feedbackMessage').val();
            if (message.length < 20) {
                alert('Please provide more detailed feedback (minimum 20 characters).');
                return;
            }

            const formData = {
                id: $('#feedbackId').val() || Date.now(),
                dateSubmitted: new Date().toISOString().split('T')[0],
                category: $('#feedbackCategory').val(),
                subject: $('#feedbackSubject').val(),
                priority: $('#feedbackPriority').val(),
                status: 'Pending',
                message: message,
                relatedChild: $('#relatedChild').val(),
                contactMethod: $('#contactMethod').val(),
                anonymous: $('#anonymousFeedback').prop('checked'),
                response: '',
                responseDate: '',
                respondedBy: ''
            };

            if ($('#feedbackId').val()) {
                // Update existing
                const index = feedbackData.findIndex(f => f.id == formData.id);
                if (index > -1) {
                    feedbackData[index] = { ...feedbackData[index], ...formData };
                    showNotification('Feedback updated successfully');
                }
            } else {
                // Add new
                feedbackData.push(formData);
                showNotification('Feedback submitted successfully');
            }

            applyFilters();
            $('#feedbackModal').modal('hide');
        });

        // Filter events
        $('#categoryFilter, #statusFilter, #priorityFilter').change(function () {
            applyFilters();
        });

        $('#clearFilters').click(function () {
            $('#categoryFilter, #statusFilter, #priorityFilter').val('');
            applyFilters();
        });

        // Edit button in view modal
        $('#editFeedbackBtn').click(function () {
            const feedbackId = $(this).data('feedback-id');
            $('#viewFeedbackModal').modal('hide');
            setTimeout(() => editFeedback(feedbackId), 300);
        });

        // Character counter for feedback message
        $('#feedbackMessage').on('input', function () {
            const length = $(this).val().length;
            const minLength = 20;
            const remaining = Math.max(0, minLength - length);

            if (remaining > 0) {
                $(this).next('.text-muted').text(`${remaining} more characters required`);
            } else {
                $(this).next('.text-muted').text(`${length} characters`);
            }
        });
    });
</script>