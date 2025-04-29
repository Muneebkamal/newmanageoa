<script>
    // Store selected columns and data globally to keep track of multiple column selections
    let selectedDbColumns = [];
    let selectedData = [];
    let mappingTemplate = {}; // Global object to store the mappings
    Dropzone.autoDiscover = false;
    // Initialize Dropzone with a hidden file input and no preview
    const myDropzone = new Dropzone("#my-dropzone", {
        url: "/upload-file", // Replace with your actual upload route
        clickable: ['#newMessages', '#dropzoneContent', '#my-dropzone'], // Allow click on center text also
        maxFilesize: 10, // Maximum file size in MB
        acceptedFiles: ".csv", // Only allow CSV files
        autoProcessQueue: true, // Automatically upload the file
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        previewsContainer: false, // Disable file previews
        init: function() {
            // Success handling
            this.on("success", function(file, response) {
                $('#newMessages').addClass('d-none')
                $('#successMessage').removeClass('d-none')
                $('#uploadNext').removeClass('d-none')
                var sourceId = $('select[name="update_source_id"]').val();
                if(sourceId == '' || sourceId == null){

                }else{
                    $('#uploadNext').attr('disabled',false);
                }
                $('#uploadButton').addClass('d-none')
                $('#mappingContainer').empty();
                filePath = response.file_path;
                $('#theFilePath').val(filePath)
                getTemplates();
               

                // Populate mappingContainer with dynamic rows
                const { headers, columns } = response; // assuming response contains headers and columns

                headers.forEach((header, index) => {
                    const rowDiv = $('<div class="row d-flex align-items-center highlight mb-1"></div>');

                    // Left side: Database column
                    const leftCol = $('<div class="col-md-4"></div>');
                    const icon = $('<i class="ri-checkbox-blank-circle-line text-danger me-2"></i>'); // Danger icon (red)

                    // Convert db column from snake_case to camelCase
                    const dbColumn = columns[index].replace(/_([a-z])/g, (_, letter) => letter.toUpperCase());

                    // Add '*' to name and asin columns
                    let displayText = dbColumn;
                    if (dbColumn === 'name' || dbColumn === 'asin') {
                        displayText += ' *';
                    }

                    const columnText = $('<span class="font-weight-bold"></span>').text(displayText);
                    leftCol.append(icon).append(columnText);

                    // Arrow icon
                    const arrowCol = $('<div class="col-md-1 text-center"></div>').html(`<i class="ri-arrow-right-line fs-5"></i>`);

                    // Right side: File header select dropdown
                    const rightCol = $('<div class="col-md-6"></div>');
                    const select = $('<select class="form-select"></select>').html('<option selected></option>');

                    // Add file headers as options in the select
                    select.append(headers.map(h => `<option value="${h}">${h}</option>`).join(''));

                    rightCol.append(select);
                    rowDiv.append(leftCol, arrowCol, rightCol);
                    $('#mappingContainer').append(rowDiv);
                    // Initialize mappingTemplate with all DB columns set to null (empty)
                    columns.forEach(dbColumn => {
                        mappingTemplate[dbColumn] = null; // Set each DB column to null initially
                    });

                    // Event listener for dropdown change
                    select.on('change', function() {
                        const selectedColumn = $(this).val(); // Get the selected column (file column)

                        if (selectedColumn) {
                            mappingTemplate[dbColumn] = selectedColumn;
                            // Change the icon to green if a header is selected
                            icon.removeClass('text-danger').addClass('text-success ri-checkbox-circle-fill');
                            icon.removeClass('ri-checkbox-blank-circle-line');

                            // Make an AJAX request to fetch data for the selected column
                            $.ajax({
                                url: "{{ url('get-file-data') }}", // Replace with your actual AJAX URL
                                type: 'POST',
                                data: {
                                    column: selectedColumn,
                                    db_column: dbColumn, // Send the camelCase database column name
                                    file_path: filePath, // Include the file path from the server response
                                    _token: '{{ csrf_token() }}' // Add CSRF token for security
                                },
                                success: function(data) {
                                    // Only proceed if there is valid data (i.e., if data is not empty)
                                    if (data && data.length > 0) {
                                        const columnIndex = selectedDbColumns.indexOf(dbColumn);
                                        if (columnIndex !== -1) {
                                            // If dbColumn already exists, update the data in the table
                                            selectedData[columnIndex] = data; // Update the existing data
                                            updateTableColumn(dbColumn, data, columnIndex); // Update the table column with new data
                                        } else {
                                            // If dbColumn doesn't exist, add the new column and data
                                            selectedDbColumns.push(dbColumn); // Use camelCase database column name
                                            selectedData.push(data); // Store the new data

                                            // Append the new data to the table
                                            appendDataToTable(selectedDbColumns, selectedData, dbColumn, data);
                                        }

                                        // Save the mapping template here
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('AJAX Error:', error); // Handle errors
                                }
                            });
                        } else {
                            // Change the icon back to red if no header is selected
                            icon.removeClass('text-success ri-checkbox-circle-fill').addClass('text-danger ri-checkbox-blank-circle-line');
                            mappingTemplate[dbColumn] = null;

                            // Remove the column from the selectedDbColumns and selectedData arrays
                            const columnIndex = selectedDbColumns.indexOf(dbColumn);
                            if (columnIndex !== -1) {
                                selectedDbColumns.splice(columnIndex, 1); // Remove the column from selectedDbColumns
                                selectedData.splice(columnIndex, 1); // Remove the data from selectedData

                                // Remove the column from the table
                                removeTableColumn(dbColumn);
                            }
                        }
                    });
                });
            });
            // Error handling
            this.on("error", function(file, response) {
                console.error("Error uploading file:", response);
                alert('Error uploading file!');
            });
        }
    });
    // Function to append new data and headers to the table
    function appendDataToTable(headers, data, newHeader, newData) {
        const table = $('#dataTableNew');

        // Append the new header to the table header
        const headerRow = table.find('thead tr');
        if (headerRow.length === 0) {
            // If the header row doesn't exist, create it
            const newHeaderRow = $('<tr></tr>');
            headers.forEach(header => {
                newHeaderRow.append(`<th>${header}</th>`);
            });
            table.find('thead').append(newHeaderRow);
        } else {
            // Append new header to the existing header row
            headerRow.append(`<th>${newHeader}</th>`);
        }

        // Append the new column data to each row in the table body
        const tableRows = table.find('tbody tr');
        newData.forEach((value, rowIndex) => {
            if (tableRows.length <= rowIndex) {
                // If row doesn't exist, create it
                const newRow = $('<tr></tr>');
                headers.forEach((_, headerIndex) => {
                    const cellValue = data[headerIndex][rowIndex] || ''; // Handle cases where data may be shorter
                    newRow.append(`<td>${cellValue}</td>`);
                });
                table.find('tbody').append(newRow);
            } else {
                // If row exists, append the new data to the existing row
                const row = tableRows.eq(rowIndex);
                row.append(`<td>${value}</td>`);
            }
        });
    }
    // Function to update an existing column in the table
    function updateTableColumn(header, data, columnIndex) {
        const table = $('#dataTableNew');

        // Update header
        const headerRow = table.find('thead tr');
        const headerCell = headerRow.find('th').eq(columnIndex);
        headerCell.text(header); // Update the header text with the dbColumn name

        // Update data in each row
        const tableRows = table.find('tbody tr');
        data.forEach((value, rowIndex) => {
            const row = tableRows.eq(rowIndex);
            const cell = row.find('td').eq(columnIndex); // Find the cell in the column
            if (cell.length) {
                cell.text(value); // Update the cell value
            } else {
                row.append(`<td>${value}</td>`); // Append new data if the cell doesn't exist
            }
        });
    }
    function removeTableColumn(dbColumn) {
        const table = $('#dataTableNew'); // Reference your table

        // Find the index of the column to remove in the table headers
        const columnIndex = selectedDbColumns.indexOf(dbColumn);

        // Remove the header from the table
        table.find('thead th').eq(columnIndex).remove();

        // Remove the corresponding data column from all rows
        table.find('tbody tr').each(function() {
            $(this).find('td').eq(columnIndex).remove();
        });
    }
    // Function to save the mapping template when the button is clicked
    $('#saveTemplateBtn').on('click', function() {
        // Check if 'asin' and 'name' columns are selected
        const requiredColumns = ['name', 'asin'];
        const missingColumns = requiredColumns.filter(col => !mappingTemplate[col]);
        console.log(missingColumns);

        if (missingColumns.length > 0) {
            toastr.error(`Please map the following required fields: ${missingColumns.join(', ')}`);
            return; // Do not proceed with saving the template if required fields are missing
        }
        var nameTemplate = $('#nameTemplate').val(); // Get the template name from input field
        if (nameTemplate === '' || nameTemplate == null) {
            toastr.error('Template Name is Required'); // Show error if template name is missing
            return; // Exit the function if template name is missing
        }
        // Prepare the data to send to the server
        let mappedColumns = {};
        Object.keys(mappingTemplate).forEach(dbColumn => {
            // Only include mappings where the file column is not null
            if (mappingTemplate[dbColumn]) {
                mappedColumns[dbColumn] = mappingTemplate[dbColumn];
            }
        });

        // Send the mappingTemplate with all DB columns and selected file columns to the server
        $.ajax({
            url: "{{ url('save-mapping-template') }}", // Replace with your actual AJAX URL for saving template
            type: 'POST',
            data: {
                db_columns: mappingTemplate, // Send all DB columns (with nulls for unselected ones)
                mapped_columns: mappedColumns, // Only the selected file columns
                name:nameTemplate,
                _token: '{{ csrf_token() }}' // Add CSRF token for security
            },
            success: function(response) {
                if (response.exists) {
                    toastr.error('Template with this name already exists'); // Show error if template exists
                } else {
                    toastr.success('Template saved successfully!'); // Show success message
                    var theFilePath = $('#theFilePath').val();
                    backConfirmSection()
                    getTemplates();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error saving template:', error); // Handle errors
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const selectBox = document.getElementById('update_source_id');
        const uploadNextButton = document.getElementById('uploadNext');

        // Function to check if the select box is empty and toggle button state
        function checkSelectBox() {
            if (selectBox.value === "") {
                uploadNextButton.disabled = true; // Disable the button if no option is selected
            } else {
                uploadNextButton.disabled = false; // Enable the button if an option is selected
            }
        }

        // Initial check
        checkSelectBox();

        // Add event listener for change event
        selectBox.addEventListener('change', checkSelectBox);
    });
    document.addEventListener('DOMContentLoaded', function() {
        // Get both select boxes
        const selectBox1 = document.querySelector('#pill-justified-home-1 select[name="update_source_id"]');
        const selectBox2 = document.querySelector('#pill-justified-profile-1 select[name="update_source_id"]');

        // Function to synchronize the select boxes
        function syncSelectBoxes(source, target) {
            target.value = source.value; // Set the target select box's value to the source's value
        }

        // Add event listeners to both select boxes
        selectBox1.addEventListener('change', function() {
            syncSelectBoxes(selectBox1, selectBox2);
        });

        selectBox2.addEventListener('change', function() {
            syncSelectBoxes(selectBox2, selectBox1);
        });
    });
    // DOM Manipulation to handle the Dropzone
    document.addEventListener('DOMContentLoaded', function() {
        const dropzoneContent = document.getElementById('dropzoneContent');
        
        // Open file dialog when the div is clicked
        dropzoneContent.addEventListener('click', function() {
            myDropzone.hiddenFileInput.click(); // Simulate file input click
        });

        // Drag and drop support - show tooltip during hover
        dropzoneContent.addEventListener('dragover', function(event) {
            event.preventDefault();
            dropzoneContent.style.backgroundColor = 'rgba(0, 0, 0, 0.7)'; // Change background on drag
        });

        dropzoneContent.addEventListener('dragleave', function(event) {
            dropzoneContent.style.backgroundColor = 'rgba(0, 0, 0, 0.5)'; // Revert background on drag leave
        });

        dropzoneContent.addEventListener('drop', function(event) {
            event.preventDefault();
            myDropzone.handleFiles(event.dataTransfer.files); // Handle dropped files
            dropzoneContent.style.backgroundColor = 'rgba(0, 0, 0, 0.5)'; // Revert background after drop
        });
    });
    // Open file input when the button is clicked
    document.getElementById('uploadButton').addEventListener('click', function() {
        document.getElementById('my-dropzone').click(); // Simulate click on the hidden file input
    });
    // DOM Manipulation to handle the Dropzone
    document.addEventListener('DOMContentLoaded', function() {
        const dropzoneContent = document.getElementById('dropzoneContent');
        
        // Open file dialog when the div is clicked
        dropzoneContent.addEventListener('click', function() {
            myDropzone.hiddenFileInput.click(); // Simulate file input click
        });

        // Drag and drop support - show tooltip during hover
        dropzoneContent.addEventListener('dragover', function(event) {
            event.preventDefault();
            dropzoneContent.style.backgroundColor = 'rgba(0, 0, 0, 0.7)'; // Change background on drag
        });

        dropzoneContent.addEventListener('dragleave', function(event) {
            dropzoneContent.style.backgroundColor = 'rgba(0, 0, 0, 0.5)'; // Revert background on drag leave
        });

        dropzoneContent.addEventListener('drop', function(event) {
            event.preventDefault();
            myDropzone.handleFiles(event.dataTransfer.files); // Handle dropped files
            dropzoneContent.style.backgroundColor = 'rgba(0, 0, 0, 0.5)'; // Revert background after drop
        });
    });
    function getTemplatesSelect() {
        filePath =   $('#theFilePath').val();
        $.ajax({
            url: "{{ route('get.templates') }}", // Adjust the route as needed
            type: "GET",
            success: function(data) {
                if (data.length > 0) {
                       // Populate the select box with templates
                       $('#templateSelect').append('<option value="">Select Template</option>')
                    data.forEach(template => {
                        $('#templateSelect').append(`<option value="${template.id}">${template.name}</option>`);
                    }); 
                }
            }
        })
    }
    $('#templateSelect').on('change', function () {
        var selectedTemp = $(this).val();
        if (selectedTemp == null || selectedTemp == '') {
            $('#deleteTemplateBtn').addClass('d-none');
            $('#templateData').html(''); // Clear previous data if no template is selected
        } else {
            $('#deleteTemplateBtn').removeClass('d-none');

            // AJAX call to fetch the template details
            $.ajax({
                url: '/get-template-data',
                type: 'GET',
                data: { templateId: selectedTemp }, // Send template ID as data
                success: function (response) {
                    if (response.templateData) {
                        printTemplateData(response.templateData); // Display the data
                    } else {
                        $('#templateData').html('<p>No template data found.</p>');
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Error fetching template data: ", error);
                }
            });
        }
    });
    // Function to dynamically display the template data, excluding null values
    function printTemplateData(templateData) {
        var html = '<table class="table">';
        html += '<thead><tr><th>Template Column</th><th>Mapped DB Column</th></tr></thead>';
        html += '<tbody>';

        // Iterate over the template data dynamically
        $.each(templateData, function (key, value) {
            if (value !== null) {  // Check if the value is not null
                html += '<tr>';
                html += '<td>' + key + '</td>'; // Template field
                html += '<td>' + value + '</td>'; // Mapped DB field
                html += '</tr>';
            }
        });

        html += '</tbody></table>';

        // If no data is displayed (all fields were null), show a message
        if (html === '<table class="table"><thead><tr><th>Template Column</th><th>Mapped DB Column</th></tr></thead><tbody></tbody></table>') {
            html = '<p>No valid template data found.</p>';
        }

        $('#templateData').html(html); // Display in the templateData div
    }
    $('#deleteTemplateBtn').on('click',function(){
        var  selctedTemp = $('#deleteTemplateBtn').val();
        if(selctedTemp){
            $.ajax({
                url:"{{ url('delete-template') }}",
                type:"POST",
                data:{
                    id:selctedTemp
                },
                success:function(data){
                    toastr.success('Template deleted successfully!');
                    getTemplates()
                }
            })
        }
    })
    function getTemplates() {
        filePath =   $('#theFilePath').val();
        $.ajax({
            url: "{{ route('get.templates') }}", // Adjust the route as needed
            type: "GET",
            success: function(data) {
                const templateList = $('#templateList');
                templateList.empty(); // Clear the list before appending new items

                if (data.length > 0) {
                    data.forEach((template, index) => {
                        // Create the template radio button with label
                        const templateDiv = $(`
                            <div class="d-flex mt-2">
                                <input type="radio" class="form-check-input me-2" name="template" value="${template.id}" ${index === 0 ? 'checked' : ''}>
                                <span>${template.name}</span>
                            </div>
                        `);

                        // Append the templateDiv to the templateList
                        templateList.append(templateDiv);

                        // Preload the mapping for the first template by default
                        if (index === 0) {
                            getSingleTemplate(template.id,filePath); // Preload the first template mapping
                        }

                        // Add event listener to load the mapping when a template is selected
                        templateDiv.find('input').on('change', function() {
                            if ($(this).is(':checked')) {
                                getSingleTemplate(template.id,filePath); // Load the template mapping when selected
                            }
                        });
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching templates:', error);
            }
        });
    }
    function getSingleTemplate(id,path){
        $.ajax({
            url:"{{ url('get-sinlge-template') }}",
            type:"GET",
            data:{
                path:path,
                id:id
            },
            success:function(data){
                console.log(data);
                loadTemplateMapping(data.mappings, data.file_data)
            }
        })
    }
    function loadTemplateMapping(mappingTemplate, fileData) {
        const table = $('#dataTableOld'); 
        // Ensure the table has a <thead> and <tbody> if not present
        if (table.find('thead').length === 0) {
            table.append('<thead></thead>');
        }
        if (table.find('tbody').length === 0) {
            table.append('<tbody></tbody>');
        }
        // Clear any existing table content to start fresh
        table.find('thead').empty();
        table.find('tbody').empty();
        // Extract the selected DB columns that have non-null corresponding file headers from mappingTemplate
        const selectedColumns = Object.keys(mappingTemplate).filter(dbColumn => mappingTemplate[dbColumn] !== null);
        // Build the table header, adding an 'Index' column at the beginning
        const headerRow = $('<tr></tr>');
        headerRow.append('<th>Index</th>'); // Add the index column as the first header
        selectedColumns.forEach(dbColumn => {
            headerRow.append(`<th>${dbColumn}</th>`); // Add the DB column name as the header
        });
        table.find('thead').append(headerRow);
        // Limit to the first 5 rows
        const limitedData = fileData.slice(0, 5);

        // Append rows from the limited file data
        limitedData.forEach((row, index) => {
            const tableRow = $('<tr></tr>');

            // Add the index column as the first cell
            tableRow.append(`<td>${index + 1}</td>`); // Index starts at 1

            // For each selected column, get the corresponding data from the file row
            selectedColumns.forEach(dbColumn => {
                const fileHeader = mappingTemplate[dbColumn]; // Get file header from template
                
                // Fetch corresponding value from file data row
                const cellValue = row[fileHeader] !== undefined ? row[fileHeader] : 'N/A'; // Replace undefined with 'N/A'
                tableRow.append(`<td>${cellValue}</td>`);
            });
            table.find('tbody').append(tableRow);
        });
    }

    $(document).ready(function () {
        // Handle the click event on the button
        $('.btn-light').on('click', function () {
            $('#templateColumns').empty(); // Clear existing columns
            // Show the modal
            $('#templateModal').modal('show');
        });
        $('#uploadButton').click(function (e) {
            e.preventDefault(); // Prevent the default form submission
            // Create a FormData object to hold the file data
            var formData = new FormData($('#uploadForm')[0]);
            // Perform the AJAX request
            $.ajax({
                url: '{{ route("file.upload") }}', // Your route to upload the file
                type: 'POST',
                data: formData,
                contentType: false, // Set to false to let jQuery set the content type
                processData: false, // Set to false to prevent jQuery from processing the data
                success: function (response) {
                    // Handle the success response
                    $('#response').html('<h3>Uploaded CSV Headers</h3><ul></ul>');
                    response.headers.forEach(function(header) {
                        $('#response ul').append('<li>' + header + '</li>');
                    });
                    
                    $('#response').append('<h3>Database Columns</h3><ul></ul>');
                    response.columns.forEach(function(column) {
                        $('#response ul').append('<li>' + column + '</li>');
                    });
                },
                error: function (xhr, status, error) {
                    // Handle the error response
                    $('#response').html('<p>Error uploading file: ' + xhr.responseText + '</p>');
                }
            });
        });
          // Handle tab click event
        $('#tab-navigation a').on('click', function(e) {
            e.preventDefault(); // Prevent default action

            // Remove disabled attribute from all tabs
            $('#tab-navigation a').attr('disabled', true);

            // Activate the clicked tab and enable it
            $(this).removeAttr('disabled').tab('show');

            // Disable the other tabs
            $('#tab-navigation a').not(this).attr('disabled', true);
        });
    });
    function toggleBulkActionDropdown() {
        console.log($('input[name="sourceLeadChckbox"]:checked').length);
        if ($('input[name="sourceLeadChckbox"]:checked').length > 1) {
            $('.bulk-action-dropdown').show(); // Show bulk actions dropdown
        } else {
            $('.bulk-action-dropdown').hide(); // Hide bulk actions dropdown
        }
    }
    function singleCheck(id){
        if ($('input[name="sourceLeadChckbox"]:checked').length === $('input[name="sourceLeadChckbox"]').length) {
            $('.checkAll').prop('checked', true);
        } else {
            $('.checkAll').prop('checked', false);
        }
        // Show or hide the bulk action dropdown based on selection
        toggleBulkActionDropdown();
    }
    $(document).ready(function() {
        // "Check All" checkbox logic
        $('.checkAll').on('change', function() {
            // Check or uncheck all checkboxes
            $('input[name="sourceLeadChckbox"]').prop('checked', $(this).prop('checked'));
            
            // Show or hide the bulk action dropdown based on selection
            toggleBulkActionDropdown();
        });
        // Individual row checkbox logic
        $('input[name="sourceLeadChckbox"]').on('change', function() {
            // If all checkboxes are checked, check the "Check All" checkbox
            if ($('input[name="sourceLeadChckbox"]:checked').length === $('input[name="sourceLeadChckbox"]').length) {
                $('.checkAll').prop('checked', true);
            } else {
                $('.checkAll').prop('checked', false);
            }
            // Show or hide the bulk action dropdown based on selection
            toggleBulkActionDropdown();
        });
        $('#showAllSourceLeads').on('click', function () {
            // Find the currently active menu item with 'lead-menu-active' class
            const activeMenuLink = $('.menu-link.lead-menu-active');
            // Simulate a click event on the active menu item
            if (activeMenuLink.length) {
                activeMenuLink.trigger('click');
            } else {
                alert("No active menu link found.");
            }
        });
    });
    $('#updateLeadSource').on('click', function () {
        const selectedRows = $('input[name="sourceLeadChckbox"]:checked').map(function () {
            return $(this).val();
        }).get();
        const newLeadSource = $('#newLeadSource').val();

        if (selectedRows.length === 0 || !newLeadSource) {
            alert("Select rows and a lead source.");
            return;
        }
        // Perform AJAX request to update lead source
        $.ajax({
            url: "{{ url('/update-leads-source') }}", // Change to your backend URL
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                ids: selectedRows,
                leadSource: newLeadSource
            },
            
            success: function (response) {
                // Handle success (refresh table or update DOM)
                toastr.success('Lead source updated successfully!');
                fetchSources();
                $('.checkAll').prop('checked',false)
                if ($.fn.DataTable.isDataTable('#uploads')) {
                    $('#uploads').DataTable().ajax.reload(); 
                }
                if ($.fn.DataTable.isDataTable('#uploadsNew')) {
                    $('#uploadsNew').DataTable().ajax.reload(); 
                }
                $('#leadSourceModal').modal('hide');
            },
            error: function () {
                alert("Error updating lead source");
            }
        });
    });
    // Update Date
    $('#updateDate').on('click', function () {
        const selectedRows = $('input[name="sourceLeadChckbox"]:checked').map(function () {
            return $(this).val();
        }).get();
        const newDate = $('#newDate').val();
        if (selectedRows.length === 0 || !newDate) {
            alert("Select rows and a date.");
            return;
        }
        // Perform AJAX request to update date
        $.ajax({
            url: "{{ url('/update-date') }}", // Change to your backend URL
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                ids: selectedRows,
                date: newDate
            },
            success: function (response) {
                // Handle success (refresh table or update DOM)
                // $('#uploads').DataTable().ajax.reload(); 
                $('.checkAll').prop('checked',false)
                toastr.success("Date updated successfully");
                if ($.fn.DataTable.isDataTable('#uploads')) {
                    $('#uploads').DataTable().ajax.reload(); 
                }
                if ($.fn.DataTable.isDataTable('#uploadsNew')) {
                    $('#uploadsNew').DataTable().ajax.reload(); 
                }
                $('#dateModifyModal').modal('hide');
             

            },
            error: function () {
                alert("Error updating date");
            }
        });
    });
    $('#deleteSelected').on('click', function () {
        const selectedRows = $('input[name="sourceLeadChckbox"]:checked').map(function () {
            return $(this).val();
        }).get();
        if (selectedRows.length === 0) {
            alert("No rows selected");
            return;
        }
        // Confirm delete
        if (confirm("Are you sure you want to delete the selected rows?")) {
            // Perform AJAX request to delete rows
            $.ajax({
                url: "{{ url('delete-rows') }}", // Change to your backend URL
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: { ids: selectedRows },
                success: function (response) {
                    $('.checkAll').prop('checked',false)
                    // Handle success (refresh table or remove deleted rows from DOM)
                    $('#uploads').DataTable().ajax.reload(); 
                    if ($.fn.DataTable.isDataTable('#uploads')) {
                        $('#uploads').DataTable().ajax.reload(); 
                    }
                    if ($.fn.DataTable.isDataTable('#uploadsNew')) {
                        $('#uploadsNew').DataTable().ajax.reload(); 
                    }
                    toastr.success("Deleted successfully");
                },
                error: function () {
                    alert("Error deleting rows");
                }
            });
        }
    });


    function fileUploadView() {
        $('#table-section').addClass('d-none');
        $('#file-section').addClass('d-none');
        $('#upload-file-section').removeClass('d-none');
        $('#newMessages').removeClass('d-none');
        $('#successMessage').addClass('d-none');
            // Activate the first tab
        $('.nav-link').removeClass('active'); // Remove active class from all tabs
        // $('.nav-link:first').addClass('active'); // Add active class to the first tab
        $(`a[href="#pill-justified-home-1"]`).addClass('active')

        $('.tab-pane').removeClass('active'); // Remove active class from all tab panes
        $('.tab-pane:first').addClass('active'); // Add active class to the first tab pane
        // Clear all inputs with class 'filePathNEw'
        $('#theFilePath').val(''); // Clears the value of all inputs with class filePathNEw
    }
    // create temp section show
    function createTempSection() {
        $('#confirm-sett').addClass('d-none');
        $('#temp-create-section').removeClass('d-none');
    }
    // back confirm setting section show
    function backConfirmSection() {
        $('#confirm-sett').removeClass('d-none');
        $('#temp-create-section').addClass('d-none');
    }
    function nextTab(tabId) {
        // Get the current active tab link
        const currentActiveTab = $('.nav-link.active');

        // Activate the next tab
        $('.nav-link').removeClass('active');
        
        // Activate the selected tab
        $(`a[href="#${tabId}"]`).addClass('active');

        // Disable the previous tab
        currentActiveTab.addClass('disabled');

        // Hide all tab panes
        $('.tab-pane').removeClass('active');
        
        // Show the selected tab pane
        $(`#${tabId}`).addClass('active');
    }

    function proceedLead() {
        var templateId = $('input[name="template"]:checked').val() || null;
        var sourceId = $('select[name="update_source_id"]').val();

        filePath = $('#theFilePath').val();
        $.ajax({
            url: "{{ url('process-template') }}",
            type: "POST",
            data: {
                path: filePath,
                source_id: sourceId,
                templateId: templateId,
                _token: '{{ csrf_token() }}', // Include CSRF token if needed
            },
            success: function(data) {
                console.log(data); // Handle success response
                sourceFindNew(sourceId,data.batchId)
                $('#cout').text(data.newLeadIds)
                nextTab('pill-justified-messages-1')
            },
            error: function(xhr) {
                console.error(xhr.responseJSON); // Handle error response
            }
        });
    }
</script>

