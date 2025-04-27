<script>
    let csrfToken = $('meta[name="csrf-token"]').attr('content');

    function asinNumber(asin, id, tags) {
        $('#tag-asin').text(asin);
        $('#lead-id-tag').val(id);
        $('#selected_tags').val(tags);
        var tagArray = [];
        $('#select-tag-modal').on('shown.bs.modal', function() {
            fetchTags('', tags);
        });
    }

    function tagEdit(id) {
        $('.tag-edit-' + id).addClass('d-none');
        $('.tag-action-' + id).removeClass('d-none');
        $('#tag-name-' + id).addClass('d-none');
        $('#tag-input-' + id).removeClass('d-none');
    }

    function tagEditDone(id) {
        const color = $('#colorToggle-' + id).attr('class').match(/text-(\w+)/)[1];

        $('#tag-name-' + id).removeClass('d-none');
        $('#tag-input-' + id).addClass('d-none');
        var editId = id;

        $('#colorToggle-' + editId).removeClass().addClass(
            `ri-checkbox-blank-fill d-none dropdown-toggle text-${color} tag-action-${editId}`);

        const tagName = $('#tag-input-' + editId).val();

        const data = {
            color: color,
            name: tagName
        };

        $.ajax({
            url: '/tag-update/' + editId,
            type: 'POST',
            data: data,
            headers: {
                'X-CSRF-Token': csrfToken
            },
            success: function(response) {
                tagsList();
                $('#manage-tag-modal').modal('hide');
                // tagsAlert(response.message, 'success');
                toastr.success(response.message);
            },
            error: function(xhr) {
                // tagsAlert('An error occurred.', 'danger');
                toastr.error('An error occurred.');
            }
        });

        $('.tag-edit-' + editId).removeClass('d-none');
        $('.tag-action-' + editId).addClass('d-none');
    }

    function tagDelete(id) {
        if (confirm('Are you sure you want to Delete Tag?')) {
            var delId = id
            $.ajax({
                url: '/tag-delete/' + delId,
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(response) {
                    tagsList();
                    $('#manage-tag-modal').modal('hide');
                    // tagsAlert(response.message, 'primary');
                    toastr.success(response.message);
                },
                error: function(xhr) {
                    // tagsAlert('An error occurred.', 'danger');
                    toastr.error('An error occurred.');
                }
            });
        } else {
            return false;
        }
    }

    function colorToggle(id, selectedColorClass) {
        var $iconToggle = $('#colorToggle-' + id);

        // Remove existing color classes
        $iconToggle.removeClass(function(index, className) {
            return (className.match(/(^|\s)text-\S+/g) || []).join(' ');
        });

        // Add the new color class
        $iconToggle.addClass('text-' + selectedColorClass);
    }

    function tagChecked(id) {
        const $checkbox = $(`#tag-${id}`);
        var Id = id;
        var enable = $checkbox.is(':checked') ? 1 : 0;

        $.ajax({
            url: '/tag-checked/' + Id,
            type: 'POST',
            data: {
                enable: enable
            },
            headers: {
                'X-CSRF-Token': csrfToken
            },
            success: function(response) {
                tagsGet();
            },
            error: function(xhr) {
                // tagsAlert('An error occurred.', 'danger');
                toastr.error('An error occurred.');
            }
        });
    }

    function tagsGet(query = '') {
        $.ajax({
            url: '{{ route('tags.get') }}',
            type: 'GET',
            data: {
                query: query
            },
            beforeSend: function(){
                $("#ajax-loader").hide();
                $("#blur-overlay").hide();
            },
            success: function(response) {
                if (response) {
                    $('#tags_get').empty();

                    var tags =`<div class="form-check dropdown-item ps-5 mb-2">
                            <input class="form-check-input checked-input" type="checkbox" id="tag-0" value="0" onclick="" >
                            <label class="form-check-label" for="formCheck1">
                                <span class="badge bg-light text-dark">(blank)</span>
                            </label>
                        </div>` ;
                    var tagsdata = '<option value="blank">(blank)</option>';
                    response.forEach(function(tag, index) {
                        tags += `<div class="form-check dropdown-item ps-5 mb-2">
                            <input class="form-check-input checked-input" type="checkbox" id="tag-${ tag.id }" onclick="tagChecked(${tag.id})"   ${tag.is_enable === 1 ? 'checked' : ''} value="${ tag.id }" >
                            <label class="form-check-label" for="formCheck1">
                                <span class="badge bg-${ tag.color }">${ tag.name }</span>
                            </label>
                        </div>`
                    });
                    response.forEach(function(tag, index) {
                        tagsdata += `<option value="${tag.id}">${tag.name}</option>`
                    });

                    $('#dropdownApplyTagsToSelected').empty();
                    $('#dropdownApplyTagsToSelected').append(tagsdata);
                    $('#tags_get').append(tags);
                } else {
                    alert('Failed to fetch tags.');
                }
            },
            error: function(xhr) {
                alert('An error occurred. Please try again.');
            }
        });
    }

    function tagsList() {

        $.ajax({
            url: '{{ route('tags.fetch') }}',
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    $('#tags-list').empty();

                    var tagsList = '';
                    response.data.forEach(function(tag, index) {
                        tagsList += `<div class="d-flex justify-content-between align-items-center">
                                <div class="mb-2 d-flex align-items-center">

                                <i class="ri-price-tag-3-fill fs-3 text-${ tag.color } tag-edit-${ tag.id }"></i>

                                <i id="colorToggle-${ tag.id }" onclick="colorToggle(${ tag.id }, '${ tag.color }')" class="ri-checkbox-blank-fill d-none dropdown-toggle text-${ tag.color } tag-action-${ tag.id }" 
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                                
                                <div class="dropdown-menu" style="--vz-dropdown-min-width: 1rem;">
                                    <a class="dropdown-item" href="#" onclick="colorToggle(${ tag.id }, 'primary');">
                                        <i class="ri-checkbox-blank-fill text-primary"></i>
                                    </a>
                                    <a class="dropdown-item" href="#" onclick="colorToggle(${ tag.id }, 'info');">
                                        <i class="ri-checkbox-blank-fill text-info"></i>
                                    </a>
                                    <a class="dropdown-item" href="#" onclick="colorToggle(${ tag.id }, 'success');">
                                        <i class="ri-checkbox-blank-fill text-success"></i>
                                    </a>
                                    <a class="dropdown-item" href="#" onclick="colorToggle(${ tag.id }, 'warning');">
                                        <i class="ri-checkbox-blank-fill text-warning"></i>
                                    </a>
                                    <a class="dropdown-item" href="#" onclick="colorToggle(${ tag.id }, 'danger');">
                                        <i class="ri-checkbox-blank-fill text-danger"></i>
                                    </a>
                                    <a class="dropdown-item" href="#" onclick="colorToggle(${ tag.id }, 'secondary');">
                                        <i class="ri-checkbox-blank-fill text-secondary"></i>
                                    </a>
                                    <a class="dropdown-item" href="#" onclick="colorToggle(${ tag.id }, 'dark');">
                                        <i class="ri-checkbox-blank-fill text-dark"></i>
                                    </a>
                                </div>
                                
                                <span id="tag-name-${tag.id}" class="ms-2" data-id="${tag.id}">${tag.name}</span>
                                <input id="tag-input-${tag.id}" type="text" class="tag-input d-none" value="${tag.name}" />
                            </div>

                            <div id="tag-edit-${ tag.id }" class="tag-edit-${ tag.id } tag-edit-btn" onclick="tagEdit(${ tag.id })">
                                <i class="ri-pencil-line me-2 fs-5"></i>
                            </div>
                            <div class="tag-action-${ tag.id } d-none">
                                <i class="ri-check-fill me-2 fs-5" onclick="tagEditDone(${ tag.id })"></i>
                                <br>
                                <i class="ri-delete-bin-6-line me-2 fs-5" onclick="tagDelete(${ tag.id })"></i>
                            </div>
                        </div>`
                    });

                    $('#tags-list').append(tagsList);
                } else {
                    alert('Failed to fetch tags.');
                }
            },
            error: function(xhr) {
                alert('An error occurred. Please try again.');
            }
        });
    }

    function unCheckTags() {
        $.ajax({
            url: 'tag-unchecked/',
            type: 'POST',
            headers: {
                'X-CSRF-Token': csrfToken
            },
            success: function(response) {
                tagsGet();
            },
            error: function(xhr) {
                toastr.error('An error occurred.');
            }
        });

    }

    $('#submit-tags').click(function(event) {
        event.preventDefault();

        var tagInput = $('#tag_name').val().trim();
        if (!tagInput) {
            $('#manage-tag-modal').modal('hide');
            return;
        }

        $.ajax({
            url: '{{ route('tag.store') }}',
            type: 'POST',
            data: $('#tag-form').serialize(),
            success: function(response) {
                $('#tag-form')[0].reset();
                tagsList();
                $('#manage-tag-modal').modal('hide');

                // tagsAlert(response.message, 'success');
                toastr.success(response.message);
            },
            error: function(xhr) {
                // tagsAlert('An error occurred.', 'danger');
                toastr.error('An error occurred.');
                // alert('An error occurred.');
            }
        });
    });

    function tagsAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;

        $('#tags-alert').append(alertHtml);

        setTimeout(function() {
            $('.alert').alert('close');
        }, 1500);
    }

    $(document).ready(function() {
        $('#tag-search').on('keyup', function() {
            let query = $(this).val();
            tags = $('#selected_tags').val();
            fetchTags(query, tags);
        });
        $('#iconrightInput').on('keyup', function() {
            let query = $(this).val();
            tagsGet(query);
        });

        $('.dropdown-item').on('click', function(event) {
            event.stopPropagation(); // Prevent the dropdown from closing
        });
    });

    function fetchTags(query = '', tags) {
        $.ajax({
            url: "{{ route('search.tags') }}",
            type: "GET",
            aysnc: false,
            data: {
                query: query
            },
            success: function(response) {
                let tagsHtml = '';
                if (response.length > 0) {
                    response.forEach(function(tag) {
                        tagsHtml += `
                        <div class="form-check dropdown-item ps-4 mb-2">
                            <input class="form-check-input select-leads-tags" name="lead_tags" type="checkbox" value="${tag.id}" id="tag-${tag.id}">
                            <label class="form-check-label" for="tag-${tag.id}">
                                <span class="badge bg-${tag.color}">${tag.name}</span>
                            </label>
                        </div>`;
                    });
                } else {
                    tagsHtml = '<p>No tags found.</p>';
                }
                $('#tags-list-lead').html(tagsHtml);
                setTimeout(() => {
                    tagArray = tags.split(',').map(tag => tag.trim());
                    $("input[name='lead_tags']").each(function() {
                        const checkboxValue = $(this).val();
                        if (tagArray.includes(checkboxValue)) {
                            $(this).prop('checked', true); // Check the checkbox
                        }
                    });
                }, 100);
            },
            error: function() {
                $('#tags-list-lead').html('<p>Error retrieving tags.</p>');
            }
        });
    }

    $('#submit-lead-tags').on('click', function() {
        const selectedTags = [];

        $("input:checkbox[name=lead_tags]:checked").each(function() {
            selectedTags.push($(this).val());
        });

        const tagsString = selectedTags.join(',');

        var leadId = $('#lead-id-tag').val();

        $.ajax({
            url: '/lead-tags-store/' + leadId,
            type: "POST",
            data: {
                tags: tagsString,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                toastr.success(response.message);

                let leadTagDiv = $('#leadTag_' + leadId);
                leadTagDiv.empty();
                response.newTags.forEach(tag => {
                    leadTagDiv.append(
                        `<span class="ms-1 badge bg-${tag.color}">${tag.name}</span>`);
                });
                $('#select-tag-modal').modal('hide');
            },
            error: function() {
                toastr.error('Error saving tags.');
            }
        });
    });
</script>
