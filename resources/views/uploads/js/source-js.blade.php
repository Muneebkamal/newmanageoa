<script>
    fetchSources();
    function sourceAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;

        $('#source-alert').append(alertHtml);

        setTimeout(function() {
            $('.alert').alert('close');
        }, 2000);
    }
    function sourceFind(id,type='all') {
        if(type== 'new'){
            fetchSources(id);
            setTimeout(() => {
                $('.source-list').find('.nav-item span').removeClass('buylist_active');
                $('.source-list').find('.nav-item span').removeClass('rejected');
                $('#sourceItemID'+id).find('span').addClass('buylist_active');
            }, 500);
        }
        var sourceId = id;
        $('#update_source_id').val(id);
        // Remove active class from other items and add to the clicked one
        $('.source-list').find('.nav-item span').removeClass('buylist_active');
        $('.source-list').find('.nav-item span').removeClass('rejected');
        $('#sourceItemID'+sourceId).find('span').addClass('buylist_active');
        $.ajax({
            url: '/sources/' + sourceId,
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {

                    $('#file-section').addClass('d-none');
                    $('#upload-file-section').addClass('d-none');
                    $('#table-section').removeClass('d-none');
                    leadsTable(sourceId);

                    // console.log(response.data);
                    $('.menu-link').removeClass('lead-menu-active');
                    $('#sourceName').empty();
                    $('#source-action').empty();

                    $('#source_' + sourceId).addClass('lead-menu-active');
                    $('#sourceName').append(
                        `<span>${response.data.list_name}</span>`
                    )
                    $('#source-action').append(
                        `<button class="btn btn-sm btn-outline-secondary me-1" onclick="alert('Show Recently Uploaded')">
                            <i class="ri-eye-line me-1"></i> Show Recently Uploaded
                        </button>
                        <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#myModal"
                            onclick="editSource('${response.data.list_name}', ${response.data.id})">
                            <i class="ri-pencil-line me-1"></i> Rename
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="sourceDelete(${response.data.id})">
                            <i class="ri-delete-bin-line me-1"></i> Delete
                        </button>`
                    );
                } else {
                    alert('Failed to fetch source details.');
                }
            },
            error: function(xhr) {
                alert('An error occurred. Please try again.');
            }
        });
        $('#sourceItemID'+sourceId).find('span').addClass('buylist_active');
        $('#selectSource').val(sourceId);
    }
    function sourceFindNew(id,batchId=null) {
        fetchSources(id);
        var sourceId = id;
        $.ajax({
            url: '/sources/' + sourceId,
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {

                    // $('#file-section').addClass('d-none');
                    // $('#upload-file-section').addClass('d-none');
                    // $('#table-section').removeClass('d-none');
                    leadsTableNew(sourceId,batchId);

                    // console.log(response.data);
                    $('.menu-link').removeClass('lead-menu-active');
                    $('#sourceNameNew').empty();
                    $('#source-actionNew').empty();
                    console.log(sourceId)

                    $('#source_' + sourceId).addClass('lead-menu-active');
                    $('#sourceNameNew').append(
                        `<span>${response.data.list_name}</span>`
                    )
                    $('#source-actionNew').append(
                        `<button class="btn btn-sm btn-light" data-bs-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="false" style="border-radius: 50%;">
                        <i class="mdi mdi-dots-vertical fs-5"></i>
                        </button>
                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="#">Show Recently Uploaded</a>
                            <div class="dropdown-divider"></div>
                            <a data-bs-toggle="modal" data-bs-target="#myModal" class="dropdown-item" href="#" onclick="editSource('${response.data.list_name}', ${response.data.id})">
                                <i class="ri-pencil-line text-primary me-2"></i> Rename Lead Source</a>
                            <a class="dropdown-item" onclick="sourceDelete(${response.data.id})">
                                <i class="ri-delete-bin-line text-danger me-2"></i> Delete Lead Source</a>
                        </div>`
                    )
                } else {
                    alert('Failed to fetch source details.');
                }
            },
            error: function(xhr) {
                alert('An error occurred. Please try again.');
            }
        });
    }

    function editSource(name, id) {
        var editName = name;
        var editId = id;
        $('#list_name').val(editName);
        $('#edit_id').val(id);

        $('#update-btn').removeClass('d-none');
        $('#create-btn').addClass('d-none');
    }

    function formClear() {
        $('#update-btn').addClass('d-none');
        $('#create-btn').removeClass('d-none');
        $('#list_name').val('');
        $('#edit_id').val('');
    }

    function sourceDelete(id) {
        if (confirm('Are you sure you want to Delete Source ?')) {
            var delId = id
            $.ajax({
                url: '/source-delete/' + delId,
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(response) {
                    fetchSources();
                    // sourceAlert(response.message, 'primary');
                    toastr.success(response.message);
                },
                error: function(xhr) {
                    // sourceAlert('An error occurred.', 'danger');
                    toastr.error('An error occurred.');
                }
            });
        } else {
            return false;
        }
    }
    // var init = true;
    function fetchSources(sourceId) {
        $.ajax({
            url: '{{ route('sources.fetch') }}',
            type: 'GET',
            async : false,
            success: function(response) {
                if (response.status === 'success') {
                    $('.source-list').empty();
                    $('.source-list').append(
                        `<li class="menu-title" style="cursor:pointer;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="p-0 my-2 d-flex align-items-center" data-key="t-menu">Lead List Sources
                                    <i class="ms-1 las la-info-circle d-block fs-5"></i>
                                </span>
                                <i onclick="formClear()" data-bs-toggle="modal" data-bs-target="#myModal"
                                    class="las la-plus d-block fs-5 text-primary"></i>
                            </div>
                        </li>`
                    )

                    $('.lead-source-list').empty();
                    $('.lead-source-list2').empty();
                    $('.lead-source-list3').empty();
                    $('.lead-source-list4').empty();

                    let sourceOptions = ``;
                    let sourceListItems = '';
                    response.data.forEach(function(source, index) {
                        // Build the list item for sources
                        sourceListItems += `<li class="nav-item mt-1" id="sourceItemID${source.id}" data-buylist-id="${source.id}" onclick="sourceFind(${source.id})">
                            <span class="badge rounded-pill bg-secondary-subtle text-secondary" style="font-size:14px;cursor:pointer;">
                                <a style="cursor: pointer" class="px-3 nav-link menu-link">
                                <strong><i class="ri-folder-line"></i> ${source.list_name}</strong> 
                                </a>
                            </span>
                        </li>`
                        // `
                        // <li class="nav-item">
                        //     <a style="cursor: pointer"  class="px-3 nav-link menu-link lead-menu-hover" id="source_${source.id}" onclick="sourceFind(${source.id})">
                        //         <i class="ri-folder-line"></i> <span data-key="t-widgets">${source.list_name}</span>
                        //     </a>
                        // </li>`;

                        // Build the dropdown options
                        sourceOptions +=
                            `<option value="${source.id}" ${source.id == sourceId ? 'selected' : ''}>${source.list_name}</option>`;
                    });
                    var blankOption = `<option disabled selected></option>`;
                    $('.source-list').append(sourceListItems);
                    $('.lead-source-list').append(sourceOptions);
                    $('.lead-source-list2').append(sourceOptions);
                    $('.lead-source-list3').append(blankOption+ sourceOptions);
                    $('.lead-source-list4').append(sourceOptions);

                    var sourceName = $('.lead-source-list option:selected').text();
                    $('#lead-source-trans').text(sourceName);
                } else {
                    alert('Failed to fetch sources.');
                }
            },
            error: function(xhr) {
                alert('An error occurred. Please try again.');
            }
        });
    }
    $('#create-btn').click(function(event) {
        event.preventDefault();

        $.ajax({
            url: '{{ route('source.create') }}',
            type: 'POST',
            data: $('#source-form').serialize(),
            success: function(response) {
                $('#source-form')[0].reset();
                $('#close-btn').click();

                sourceFind(response.data,'new');
                // sourceAlert(response.message, 'success');
                toastr.success(response.message);
            },
            error: function(xhr) {
                // sourceAlert('An error occurred.', 'danger');
                toastr.error('An error occurred.');
            }
        });
    });
    $('#update-btn').click(function(event) {
        event.preventDefault();
        var editId = $('#edit_id').val();

        $.ajax({
            url: '/source-update/' + editId,
            type: 'POST',
            data: $('#source-form').serialize(),
            success: function(response) {
                $('#source-form')[0].reset();
                $('#close-btn').click();

                sourceFind(response.data);
                toastr.success(response.message);
                // sourceAlert(response.message, 'success');
            },
            error: function(xhr) {
                // sourceAlert('An error occurred.', 'danger');
                toastr.error('An error occurred.');
            }
        });
    });
</script>
