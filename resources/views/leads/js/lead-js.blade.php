<script>
    let sourceItemCount = 0;
    let currentIndex = 0;
    const sourceItems = [];

    $('#add-source-item').on('click', function() {
        // Save data before adding a new item
        saveCurrentItemData();

        // Increment and add a new item
        sourceItemCount++;
        sourceItems.push({}); // Initialize a new item
        currentIndex = sourceItemCount - 1; // Set currentIndex to the new item
        updateSourceItemDisplay();
    });

    function createSourceItemForm(index) {
        return `
            <div class="row source-item mt-3" data-index="${index}">
                <div class="col-md-12 mt-3">
                    <div class="mb-3">
                        <label for="item_name_${index}">Item Name*</label>
                        <input type="text" class="form-control" id="item_name_${index}" name="item_name[]" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="item_supplier_${index}">Supplier*</label>
                        <input type="text" class="form-control" id="item_supplier_${index}" name="item_supplier[]" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="item_promo_${index}">Promo</label>
                        <input type="text" class="form-control" id="item_promo_${index}" name="item_promo[]">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="item_url_${index}">Source URL*</label>
                        <div class="input-group">
                            <span class="input-group-text">https://</span>
                            <input type="text" class="form-control" id="item_url_${index}" name="item_url[]" required>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="item_coupon_${index}">Coupon Code</label>
                        <input type="text" class="form-control" id="item_coupon_${index}" name="item_coupon[]">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="item_cost_${index}">Cost*</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="item_cost_${index}" name="item_cost[]" required>
                        </div>
                      
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="item_notes_${index}">Lead Notes</label>
                        <textarea class="form-control" id="item_notes_${index}" name="item_notes[]"></textarea>
                    </div>
                </div>  
            </div>
        `;
    }

    function updateSourceItemDisplay() {
        $('#source-items-container').html(sourceItems.map((_, index) => createSourceItemForm(index + 1))
            .join(''));
        loadCurrentItemData(); // Load data for the current index
        $('#source-item-count').text(`Source Item ${currentIndex + 1} of ${sourceItemCount}`);
        $('#prev-item').toggleClass('d-none', currentIndex === 0);
        $('#next-item').toggleClass('d-none', currentIndex === sourceItemCount - 1);
        displayCurrentItem(); // Show the current item
    }

    $('#prev-item').on('click', function() {
        if (currentIndex > 0) {
            saveCurrentItemData();
            currentIndex--;
            updateSourceItemDisplay();
        }
    });

    $('#next-item').on('click', function() {
        if (currentIndex < sourceItemCount - 1) {
            saveCurrentItemData();
            currentIndex++;
            updateSourceItemDisplay();
        }
    });

    function displayCurrentItem() {
        $('.source-item').hide().eq(currentIndex).show(); // Only show the current item
    }

    function saveCurrentItemData() {
        if (sourceItems[currentIndex]) {
            const itemIndex = currentIndex + 1;
            const currentItem = sourceItems[currentIndex];

            currentItem.name = $(`#item_name_${itemIndex}`).val();
            currentItem.supplier = $(`#item_supplier_${itemIndex}`).val();
            currentItem.promo = $(`#item_promo_${itemIndex}`).val();
            currentItem.url = $(`#item_url_${itemIndex}`).val();
            currentItem.coupon = $(`#item_coupon_${itemIndex}`).val();
            currentItem.cost = $(`#item_cost_${itemIndex}`).val();
            currentItem.notes = $(`#item_notes_${itemIndex}`).val();
        }
    }

    function loadCurrentItemData() {
        const currentItem = sourceItems[currentIndex] || {};
        const itemIndex = currentIndex + 1;

        $(`#item_name_${itemIndex}`).val(currentItem.name || '');
        $(`#item_supplier_${itemIndex}`).val(currentItem.supplier || '');
        $(`#item_promo_${itemIndex}`).val(currentItem.promo || '');
        $(`#item_url_${itemIndex}`).val(currentItem.url || '');
        $(`#item_coupon_${itemIndex}`).val(currentItem.coupon || '');
        $(`#item_cost_${itemIndex}`).val(currentItem.cost || '');
        $(`#item_notes_${itemIndex}`).val(currentItem.notes || '');
    }

    $('#add-lead').click(function(event) {
        event.preventDefault();

        saveCurrentItemData();
        const formData = $('#lead-form').serializeArray();

        // Prepare the items array
        const itemsArray = sourceItems.map(item => ({
            name: item.name,
            supplier: item.supplier,
            promo: item.promo || '',
            url: item.url,
            coupon: item.coupon || '',
            cost: item.cost,
            notes: item.notes || ''
        }));

        const dataToSend = {};

        // Append additional form data first
        formData.forEach(function(item) {
            dataToSend[item.name] = item.value;
        });

        // Then add the items array at the end
        dataToSend.item = itemsArray;

        // Send the data via AJAX
        $.ajax({
            url: '{{ route('lead.add') }}',
            type: 'POST',
            data: JSON.stringify(dataToSend),
            contentType: 'application/json',
            success: function(response) {
                $('#lead-form')[0].reset();
                $('.close-btn').click();

                leadsTable(response.data);
                // showAlert(response.message, 'secondary'); 
                toastr.success(response.message);
            },
            error: function(xhr) {
                // showAlert('An error occurred.', 'danger'); // Show error alert
                toastr.error('An error occurred.');
            }
        });
    });

    // Initially, add the first item if needed
    if (sourceItemCount === 0) {
        $('#add-source-item').click();
    }


    function showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;

        $('#leads-alert').append(alertHtml);

        setTimeout(function() {
            $('.alert').alert('close');
        }, 1500);
    }

    $('#bundle').change(function() {
        if ($(this).is(':checked')) {
            $("#source-items-section").removeClass('d-none');
        } else {
            $("#source-items-section").addClass('d-none');
        }
    });

    function leadsTable(id) {
        var sourceId = id;

        if ($.fn.DataTable.isDataTable("#uploads")) {
            $("#uploads").DataTable().destroy();
        }

        gb_DataTable = $("#uploads").DataTable({
            autoWidth: false,
            order: [0, "ASC"],
            processing: true,
            serverSide: true,
            searchDelay: 2000,
            paging: true,
            ajax: {
            url: "leads-fetch/" + sourceId,
                data: function (d) {
                    // Pass sort field and order direction to the server
                    d.sortField = $('#inputSortSelect').val();
                    d.sortOrder = $('#orderbyinput').val();
                }
            },
            iDisplayLength: "10",
            columns: [{
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'asin',
                    name: 'asin'
                },
                {
                    data: 'url',
                    name: 'url'
                },
                {
                    data: 'category',
                    name: 'category'
                },
                {
                    data: 'cost',
                    name: 'cost'
                },
                {
                    data: 'sell_price',
                    name: 'sell_price'
                },
                {
                    data: 'net_profit',
                    name: 'net_profit'
                },
                {
                    data: 'quantity',
                    name: 'quantity'
                },
                {
                    data: 'notes',
                    name: 'notes'
                },
                {
                    data: 'date',
                    name: 'date'
                },
            ],
            lengthMenu: [10,25, 50, 100]
        });
        $('#inputSortSelect, #orderbyinput').on('change', function () {
            gb_DataTable.ajax.reload(); // Reload the DataTable
        });
    }
    function leadsTableNew(id,batchId=null) {
        var sourceId = id;

        if ($.fn.DataTable.isDataTable("#uploadsNew")) {
            $("#uploadsNew").DataTable().destroy();
        }

        gb_DataTable = $("#uploadsNew").DataTable({
            autoWidth: false,
            order: [0, "ASC"],
            processing: true,
            serverSide: true,
            searchDelay: 2000,
            paging: true,
            ajax: {
                url: "leads-fetch-new/" + sourceId, // URL for the getProcessedLeads method
                data: {
                    batchId:batchId, // Pass newLeadIds to the server
                }
            },
            iDisplayLength: "25",
            columns: [{
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'asin',
                    name: 'asin'
                },
                {
                    data: 'url',
                    name: 'url'
                },
                {
                    data: 'category',
                    name: 'category'
                },
                {
                    data: 'cost',
                    name: 'cost'
                },
                {
                    data: 'sell_price',
                    name: 'sell_price'
                },
                {
                    data: 'notes',
                    name: 'notes'
                },
                {
                    data: 'date',
                    name: 'date'
                },
            ],
            lengthMenu: [25, 50, 100]
        });
    }

    function leadFind(id) {
        var leadId = id;

        $.ajax({
            url: '/lead/' + leadId,
            type: 'GET',
            success: function(response) {

                $('#lead_id').val(response.data.id);

                if (response.status === 'success') {
                    
                    $('#add-lead').addClass('d-none');
                    $('#lead-add-time').addClass('d-none');
                    $('#update-lead').removeClass('d-none');
                    $('#lead-update-time').removeClass('d-none');
                    $('#exampleModalScrollableTitle1').removeClass('d-none');
                    $('#exampleModalScrollableTitle').addClass('d-none');

                    if (response.data.bundle == 1) {
                        $('#bundle').prop('checked', true);
                    } else {
                        $('#bundle').prop('checked', false);
                    }
                    $('#date').val(response.data.date);
                    if(response.data.created_by != null){
                        $('#created_by_name').text(response.data.created_by.name);
                    }
                    $('#lead-date-update').text(response.data.date);
                    $('#name').val(response.data.name);
                    $('#asin').val(response.data.asin);
                    $('#cost').val(response.data.cost);
                    $('#category').val(response.data.category);
                    $('#sell_price').val(response.data.sell_price).trigger('change');
                    let domain = response.data.url;
                    if (response.data.url) {
                        try {
                            domain = new URL(response.data.url).hostname;
                        } catch (e) {
                            console.error('Invalid URL:', response.data.url);
                        }
                    }
                    $('#supplier').val(domain).trigger('change');
                    $('#net_profit').val(response.data.net_profit).trigger('change');
                    $('#url').val(response.data.url);
                    var roi = response.data.roi !== null ? parseFloat(response.data.roi) : (response.data.cost > 0 ? (response.data.net_profit / response.data.cost) : 0);
                    var display_roi = roi * 100;
                    $('#item_roi').val(roi.toFixed(2));
                    $('#roi_display').text(display_roi.toFixed(2) + '%');

                    $('#item_bsr').val(response.data.bsr);
                    $('#promo').val(response.data.promo);
                    $('#currency').val(response.data.currency == null?'USD':response.data.currency );
                    $('#coupon').val(response.data.coupon);
                    $('#notes').val(response.data.notes);
                    $('.amazonUrl').attr('href','https://www.amazon.com/dp/'+response.data.asin+'')
                    $('.source_url').attr('href',response.data.url)
                    if (response.data.is_hazmat == 1) {
                        $('#is_hazmat').prop('checked', true);
                    } else {
                        $('#is_hazmat').prop('checked', false);
                    }
                    if (response.data.is_disputed == 1) {
                        $('#is_disputed').prop('checked', true);
                    } else {
                        $('#is_disputed').prop('checked', false);
                    }
                    updateCalculations();
                } else {
                    alert('Failed to fetch lead details.');
                }
            },
            error: function(xhr) {
                alert('An error occurred. Please try again.');
            }
        });
        updateCalculations();
    }
    $('#asin').on('input',function(){
        $('.amazonUrl').attr('href','https://www.amazon.com/dp/'+$(this).val()+'')
    })
    $('#url').on('input',function(){
        $('.source_url').attr('href',$(this).val())
    })
    $('#orderSourceUrl').on('input',function(){
        $('.source_url').attr('href',$(this).val())
    })
    $('#orderAsin').on('input',function(){
        $('#amazonUrl').attr('href','https://www.amazon.com/dp/'+$(this).val()+'')
    })

    function formLeadClear() {
        $('#update-lead').addClass('d-none');
        $('#add-lead').removeClass('d-none');
        $('#lead-add-time').removeClass('d-none');
        $('#lead-update-time').addClass('d-none');
        $('#exampleModalScrollableTitle').removeClass('d-none');
        $('#exampleModalScrollableTitle1').addClass('d-none');
        $('#lead-form')[0].reset();
        $('.lead-source-list2').val($('#selectSource').val()).trigger('change');
    }

    $('#update-lead').click(function(event) {
        event.preventDefault();
        var editId = $('#lead_id').val();

        $.ajax({
            url: 'lead-update/' + editId,
            type: 'POST',
            data: $('#lead-form').serialize(),
            success: function(response) {
                $('#lead-form')[0].reset();
                $('.close-btn').click();
                leadsTable(response.data);
                // showAlert(response.message, 'success');
                toastr.success(response.message);
                if (window.location.pathname === '/leads') {
                    window.location.reload();
                }
            },
            error: function(xhr) {
                // showAlert('An error occurred.', 'danger');
                toastr.error('An error occurred.');
            }
        });
    });

    function leadDelete(id) {
        if (confirm('Are you sure you want to Delete Lead ?')) {
            var delId = id
            $.ajax({
                url: '/lead-delete/' + delId,
                type: 'POST',
                data: {
                    "_token": "{{ csrf_token() }}"
                },
                success: function(response) {
                    leadsTable(response.data);
                    // showAlert(response.message, 'primary');
                    toastr.success(response.message);
                },
                error: function(xhr) {
                    // showAlert('An error occurred.', 'danger');
                    toastr.error('An error occurred.');
                }
            });
        } else {
            return false;
        }
    }

    function sourceTransfer() {
        $('.transfer-source').toggleClass('d-none');
    }
    function openModal(id,is_buylist =0){
        $.ajax({
            url:"{{ url('get-single-lead') }}",
            type:"GET",
            data:{
                id:id
            },
            success:function(data){
                if(data){
                    msku = getMsku(data.asin);
                    // console.log(msku);
                    if(is_buylist == 0){
                        if(data.bundle == 0){
                            $('#bundleModal #orderName').val(data.name)
                            $('#bundleModal #msku').val(msku)
                            $('#bundleModal #msku').attr('readonly',true)
                            $('#bundleModal #orderAsin').val(data.asin)
                            $('#bundleModal #orderCost').val(data.cost)
                            $('#bundleModal #orderCategory').val(data.category)
                            $('#bundleModal #orderSellingPrice').val(data.sell_price)
                            let domain = data.url;
                            if (data.url) {
                                try {
                                    domain = new URL(data.url).hostname;
                                } catch (e) {
                                    console.error('Invalid URL:', data.url);
                                }
                            }
                            $('#bundleModal #orderSupplier').val(domain)
                            $('#bundleModal #orderPromo').val(data.promo)
                            $('#bundleModal #orderSourceUrl').val(data.url)
                            $('#bundleModal #orderProductNote').val(data.notes)
                            $('#bundleModal #orderBsr').val(data.bsr)
                            $('#bundleModal #amazonUrl').attr('href','https://www.amazon.com/dp/'+data.asin+'')
                            $('#bundleModal .source_url').attr('href',data.url)
                            if(data.is_disputed == 1){
                                $('#bundleModal #orderIsDisputed').prop('checked',true).trigger('change');
                            }
                            if(data.is_hazmat == 1){
                                $('#bundleModal #orderIsHazmat').prop('checked',true).trigger('change');
                            }
                            $('#bundleModal').modal('show')
                        }else{
                            $('#addBundleCreateOrder #msku').val(msku)
                            $('#addBundleCreateOrder #msku').attr('readonly',true)
                            $('#addBundleCreateOrder #editNameCreateOrderItem').val(data.name)
                            $('#addBundleCreateOrder #editAsinCreateOrderItem').val(data.asin)
                            $('#addBundleCreateOrder #editCostCreateOrderItem').val(data.cost)
                            $('#addBundleCreateOrder #editCategoryCreateOrderItem').val(data.category)
                            $('#addBundleCreateOrder #editSellingPriceCreateOrderItem').val(data.sell_price)
                            $('#addBundleCreateOrder #editSupplierCreateOrderItem').val(data.supplier)
                            $('#addBundleCreateOrder #orderPromo').val(data.promo)
                            $('#addBundleCreateOrder #orderSourceUrl').val(data.url)
                            $('#addBundleCreateOrder #editProductNoteCreateOrderItem').val(data.notes)
                            $('#addBundleCreateOrder #editNinetyDayAverageCreateOrderItem').val(data.bsr)
                            $('#addBundleCreateOrder #amazonUrl').attr('href','https://www.amazon.com/dp/'+data.asin+'')
                            $('#addBundleCreateOrder .source_url').attr('href',data.url)
                            $('#addBundleCreateOrder').modal('show');
                            if(data.is_disputed == 1){
                                $('#addBundleCreateOrder #editOrderIsDisputed').prop('checked',true).trigger('change');
                            }
                            if(data.is_hazmat == 1){
                                $('#addBundleCreateOrder #editOrderIsHazmat').prop('checked',true).trigger('change');
                            }
                            if(data.bundle == 1 ){
                                if (data.bundle === 1 && data.bundles.length > 0) {
                                    let currentBundleIndex = 0; // Track the current bundle index
                                    populateBundleSection(data.bundles,currentBundleIndex,'#addBundleCreateOrder');
                                    bundles = data.bundles
                                    
                                    // Event listener for the Next button
                                    $('#addBundleCreateOrder #nextBtn').on('click', function() {
                                        if (currentBundleIndex < bundles.length - 1) {
                                            currentBundleIndex++;
                                            displayBundle(currentBundleIndex,data.bundles,'#addBundleCreateOrder');
                                        }
                                    });
                                    // Event listener for the Previous button
                                    $('#addBundleCreateOrder #prevBtn').on('click', function() {
                                        if (currentBundleIndex > 0) {
                                            currentBundleIndex--;
                                            displayBundle(currentBundleIndex,data.bundles,'#addBundleCreateOrder');
                                        }
                                    });
                                } else {
                                    $('#addBundleCreateOrder #bundleSection').html('<p>No bundles available.</p>');
                                }
                            }
                            // $('#addBundleCreateOrder').modal('show');

                        }
                       
                    }else{
                        if(data.bundle == 0){
                            $('#buyListLeadModal #msku').val(msku)
                            $('#buyListLeadModal #msku').attr('readonly',true)
                            $('#buyListLeadModal #orderName').val(data.name)
                            $('#buyListLeadModal #orderAsin').val(data.asin)
                            $('#buyListLeadModal #orderCost').val(data.cost)
                            $('#buyListLeadModal #orderCategory').val(data.category)
                            $('#buyListLeadModal #orderSellingPrice').val(data.sell_price)
                            $('#buyListLeadModal #orderNetprofit').val(data.net_profit)
                            let domain = data.url;
                            if (data.url) {
                                try {
                                    domain = new URL(data.url).hostname;
                                } catch (e) {
                                    console.error('Invalid URL:', data.url);
                                }
                            }
                            $('#buyListLeadModal #orderSupplier').val(domain)
                            $('#buyListLeadModal #orderPromo').val(data.promo)
                            $('#buyListLeadModal #orderSourceUrl').val(data.url)
                            $('#buyListLeadModal #orderProductNote').val(data.notes)
                            $('#buyListLeadModal #orderBsr').val(data.bsr)
                            $('#buyListLeadModal #amazonUrl').attr('href','https://www.amazon.com/dp/'+data.asin+'')
                            $('#buyListLeadModal .source_url').attr('href',data.url)
                            $('#buyListLeadModal').modal('show');
                            if(data.is_disputed == 1){
                                $('#buyListLeadModal #orderIsDisputed').prop('checked',true).trigger('change');
                            }
                            if(data.is_hazmat == 1){
                                $('#buyListLeadModal #orderIsHazmat').prop('checked',true).trigger('change');
                            }
                        }else{
                            $('#addBundleBuyList #msku').val(msku)
                            $('#addBundleBuyList #msku').attr('readonly',true)
                            $('#addBundleBuyList #editNameCreateOrderItem').val(data.name)
                            $('#addBundleBuyList #editAsinCreateOrderItem').val(data.asin)
                            $('#addBundleBuyList #editCostCreateOrderItem').val(data.cost)
                            $('#addBundleBuyList #editCategoryCreateOrderItem').val(data.category)
                            $('#addBundleBuyList #editSellingPriceCreateOrderItem').val(data.sell_price)
                            $('#addBundleBuyList #editSupplierCreateOrderItem').val(data.supplier)
                            $('#addBundleBuyList #orderPromo').val(data.promo)
                            $('#addBundleBuyList #orderSourceUrl').val(data.url)
                            $('#addBundleBuyList #editProductNoteCreateOrderItem').val(data.notes)
                            $('#addBundleBuyList #editNinetyDayAverageCreateOrderItem').val(data.bsr)
                            $('#addBundleBuyList #amazonUrl').attr('href','https://www.amazon.com/dp/'+data.asin+'')
                            $('#addBundleBuyList .source_url').attr('href',data.url)
                            $('#addBundleBuyList').modal('show');
                            if(data.is_disputed == 1){
                                $('#addBundleBuyList #editOrderIsDisputed').prop('checked',true).trigger('change');
                            }
                            if(data.is_hazmat == 1){
                                $('#addBundleBuyList #editOrderIsHazmat').prop('checked',true).trigger('change');
                            }
                            if(data.bundle == 1 ){
                                if (data.bundle === 1 && data.bundles.length > 0) {
                                    let currentBundleIndex = 0; // Track the current bundle index
                                    populateBundleSection(data.bundles,currentBundleIndex,'#addBundleBuyList');
                                    bundles = data.bundles
                                    
                                    // Event listener for the Next button
                                    $('#addBundleBuyList #nextBtn').on('click', function() {
                                        if (currentBundleIndex < bundles.length - 1) {
                                            currentBundleIndex++;
                                            displayBundle(currentBundleIndex,data.bundles,'#addBundleBuyList');
                                        }
                                    });
                                    // Event listener for the Previous button
                                    $('#addBundleBuyList #prevBtn').on('click', function() {
                                        if (currentBundleIndex > 0) {
                                            currentBundleIndex--;
                                            displayBundle(currentBundleIndex,data.bundles,'#addBundleBuyList');
                                        }
                                    });
                                } else {
                                    $('#bundleSection').html('<p>No bundles available.</p>');
                                }
                            }
                            $('#addBundleBuyList').modal('show');
                        }
                        
                    }
                   
                }
            }
        })
        updateOrderCalculations();
       
    }
    function getMsku(asin){
        var msku ='';
        $.ajax({
            url : `{{ url('get-msku') }}`,
            type : "GET",
            data:{
                asin:asin
            },
            async:false,
            success:function(data){
                msku = data;
            }

        })
        return msku;
    }
    // Function to display the current bundle
    function displayBundle(index,bundles,modalId) {
        $('.bundle-item').hide();  // Hide all bundles
        $(`${modalId} #bundle${index}`).show();  // Show only the current bundle
        $(`${modalId} #prevBtn`).toggle(index > 0); // Show 'Previous' if not on first bundle
        $(`${modalId} #nextBtn`).toggle(index < bundles.length - 1); // Show 'Next' if not on last bundle
        $(`${modalId} #addToBuyListBtn`).toggle(index === bundles.length - 1); // Show 'Add to Buy List' on the last bundle
    }
    // Function to initialize the bundle section with navigation buttons
    function populateBundleSection(bundles,currentBundleIndex,ModalID) {
        $(`${ModalID} #bundleSection`).empty();  // Clear existing content
        bundles.forEach((bundle, index) => {
            const bundleHTML = `<div id="bundle${index}" class="bundle-item" style="display: none;">
                <h5>Bundle ${index + 1} of ${bundles.length}</h5>
                <form class="form-group justify-content-center">
                    <div class="form-group row">
                        <div class="col">
                            <label for="bundleeditNameCreateOrderItem${bundle.id}" class="col-form-label">Name</label>
                            <textarea rows="1" id="bundleeditNameCreateOrderItem${bundle.id}" class="form-control">${bundle.name}</textarea>
                        </div>
                    </div>
                    <div class="form-group row">
                        <div class="col-md-6">
                            <label for="bundleditSupplierCreateOrderItem${bundle.id}" class="col-form-label">Supplier</label>
                            <input type="text" id="bundleditSupplierCreateOrderItem${bundle.id}" class="form-control" value="${bundle.supplier}" >
                            
                            <label for="bundleeditSourceUrlCreateOrderItem${bundle.id}" class="col-form-label">Source URL <a href="${bundle.url}" target="_blank">Link to source</a></label>
                            <input type="text" id="bundleeditSourceUrlCreateOrderItem${bundle.id}" class="form-control" value="${bundle.url}">
                        </div>
    
                        <div class="col-md-6">
                            <label for="bundleeditCostCreateOrderItem${bundle.id}" class="col-form-label">Cost</label>
                            <input type="text" id="bundleeditCostCreateOrderItem${bundle.id}" class="form-control" value="${bundle.cost}">
                             <label for="bundleeditPromoCreateOrderItem${bundle.id}" class="col-form-label">Promo</label>
                            <input type="text" id="bundleeditPromoCreateOrderItem${bundle.id}" class="form-control" value="${bundle.promo}">
                        </div>
                        <div class="col-md-6">
                           
                        </div>
                         <div class="col-md-6">
                            <label for="bundleeditCouponCreateOrderItem${bundle.id}" class="col-form-label">Coupon Code</label>
                            <input type="text" id="bundleeditCouponCreateOrderItem${bundle.id}" class="form-control" value="${bundle.coupon}">
                        </div>
                    </div>
                </form> </div>`;
                $(`${ModalID} #bundleSection`).append(bundleHTML);  // Append bundle HTML to the bundle section
        });
        displayBundle(currentBundleIndex,bundles,ModalID); // Display the first bundle initially
    }
    function collectBundleData() {
        let bundlesData = [];
        $('.bundle-item').each(function() {
            let bundleData = {
            name: $(this).find('textarea[id^="bundleeditNameCreateOrderItem"]').val(),
            supplier: $(this).find('input[id^="bundleditSupplierCreateOrderItem"]').val(),
            url: $(this).find('input[id^="bundleeditSourceUrlCreateOrderItem"]').val(),
            cost: $(this).find('input[id^="bundleeditCostCreateOrderItem"]').val(),
            promo: $(this).find('input[id^="bundleeditPromoCreateOrderItem"]').val(),
            coupon: $(this).find('input[id^="bundleeditCouponCreateOrderItem"]').val()
        };
        // Check for duplicates
        let isDuplicate = bundlesData.some(existingBundle =>
            existingBundle.name === bundleData.name &&
            existingBundle.supplier === bundleData.supplier &&
            existingBundle.url === bundleData.url &&
            existingBundle.cost === bundleData.cost &&
            existingBundle.promo === bundleData.promo &&
            existingBundle.coupon === bundleData.coupon
        );

        // Only add if not a duplicate
        if (!isDuplicate) {
            bundlesData.push(bundleData);
        }
    });


        return bundlesData;
    }
    // Save Button AJAX Logic
    function saveOrderBundleItems(){
        let modalSelector = $('#addBundleCreateOrder').is(':visible') ? '#addBundleCreateOrder' : '#addBundleBuyList';
        // Collect order data from modal fields
        let orderData = {
            list_price: $(modalSelector + ' #editListPrice').val(),
            unit_purchased: $(modalSelector + ' #quantity').val(),
            min: $(modalSelector + ' #editMinPrice').val(),
            product_buyer_notesnotes: $(modalSelector + ' #editOrderNote').val(),
            max: $(modalSelector + ' #editMaxPrice').val(),
            msku: $(modalSelector + ' #editMsku').val(),
            name: $(modalSelector + ' #editNameCreateOrderItem').val(),
            asin: $(modalSelector + ' #editAsinCreateOrderItem').val(),
            cost: $(modalSelector + ' #editCostCreateOrderItem').val(),
            category: $(modalSelector + ' #editCategoryCreateOrderItem').val(),
            sell_price: $(modalSelector + ' #editSellingPriceCreateOrderItem').val(),
            supplier: $(modalSelector + ' #editSupplierCreateOrderItem').val(),
            promo: $(modalSelector + ' #orderPromo').val(),
            url: $(modalSelector + ' #orderSourceUrl').val(),
            notes: $(modalSelector + ' #editProductNoteCreateOrderItem').val(),
            bsr: $(modalSelector + ' #editNinetyDayAverageCreateOrderItem').val(),
            is_disputed: $(modalSelector + ' #editOrderIsDisputed').is(':checked') ? 1 : 0,
            is_hazmat: $(modalSelector + ' #editOrderIsHazmat').is(':checked') ? 1 : 0,
            // Collect all bundle data
            bundles: collectBundleData()
        };

        $.ajax({
            url: `{{ url('save-order-bundle-items') }}`, // Replace with your endpoint
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            data: orderData,
            success: function(response) {
                if (response.success) {
                    toastr.success('Order and item saved successfully!');
                    window.open(`list/buycostcalculator/${response.id}`, '_blank');
                    $(modalSelector).modal('hide');
                } else {
                    alert("Failed to save data.");
                }
            },
            error: function(err) {
                console.error("Error saving data:", err);
            }
        });
    }
    function saveBuyListBundleItems(){
        // let modalSelector = $('#addBundleCreateOrder').is(':visible') ? '#addBundleCreateOrder' : '#addBundleBuyList';
        // Collect order data from modal fields
        const buylistId = $('#buyId').val();
        let orderData = {
            list_price: $('#addBundleBuyList #editListPrice').val(),
            unit_purchased: $('#addBundleBuyList  #quantity').val(),
            min: $('#addBundleBuyList  #editMinPrice').val(),
            product_buyer_notesnotes: $('#addBundleBuyList #editOrderNote').val(),
            max: $('#addBundleBuyList #editMaxPrice').val(),
            msku: $('#addBundleBuyList #editMsku').val(),
            name: $('#addBundleBuyList #editNameCreateOrderItem').val(),
            asin: $('#addBundleBuyList #editAsinCreateOrderItem').val(),
            cost: $('#addBundleBuyList  #editCostCreateOrderItem').val(),
            category: $('#addBundleBuyList  #editCategoryCreateOrderItem').val(),
            sell_price: $('#addBundleBuyList #editSellingPriceCreateOrderItem').val(),
            supplier: $('#addBundleBuyList  #editSupplierCreateOrderItem').val(),
            promo: $('#addBundleBuyList  #orderPromo').val(),
            url: $('#addBundleBuyList  #orderSourceUrl').val(),
            notes: $('#addBundleBuyList  #editProductNoteCreateOrderItem').val(),
            bsr: $('#addBundleBuyList  #editNinetyDayAverageCreateOrderItem').val(),
            is_disputed: $('#addBundleBuyList  #editOrderIsDisputed').is(':checked') ? 1 : 0,
            is_hazmat: $('#addBundleBuyList  #editOrderIsHazmat').is(':checked') ? 1 : 0,
            buylist_id:buylistId,
            // Collect all bundle data
            bundles: collectBundleData()
        };

        $.ajax({
            url: `{{ url('save-buylist-bundle-items') }}`, // Replace with your endpoint
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            data: orderData,
            success: function(response) {
                if (response.success) {
                    toastr.success('Buylist and item saved successfully!');
                    // window.open(`list/buycostcalculator/${response.id}`, '_blank');
                    $('#addBundleBuyList').modal('hide');
                } else {
                    alert("Failed to save data.");
                }
            },
            error: function(err) {
                console.error("Error saving data:", err);
            }
        });
    }


    $(document).ready(function() {
        getSoruces()
        // Handle the plus button click
        $('.btn-number[data-type="plus"]').click(function(e) {
            e.preventDefault();
            var input = $(this).closest('.input-group').find('.input-number');
            var currentValue = parseInt(input.val());
            
            if (!isNaN(currentValue)) {
                input.val(currentValue + 1); // Increment the value
            } else {
                input.val(1); // Set to 1 if input is not a number
            }
        });
        // Handle the minus button click
        $('.btn-number[data-type="minus"]').click(function(e) {
            e.preventDefault();
            var input = $(this).closest('.input-group').find('.input-number');
            var currentValue = parseInt(input.val());

            if (!isNaN(currentValue) && currentValue > 1) {
                input.val(currentValue - 1); // Decrement the value
            } else {
                input.val(1); // Ensure value does not go below 1
            }
        });
        // Optional: Restrict direct input to only positive numbers
        $('.input-number').on('input', function() {
            var value = parseInt($(this).val());
            if (isNaN(value) || value < 1) {
                $(this).val(1); // Ensure value stays positive
            }
        });
         // When "Hazmat" checkbox is changed
        $('#bundleModal orderIsHazmat').change(function() {
            if ($(this).is(':checked')) {
                // If checked, set the button color to red
                $(this).closest('button').removeClass('btn-light').addClass('btn-danger');
            } else {
                // If unchecked, reset the button color to light
                $(this).closest('button').removeClass('btn-danger').addClass('btn-light');
            }
        });

        // When "Disputed" checkbox is changed
        $('#bundleModal #orderIsDisputed').change(function() {
            if ($(this).is(':checked')) {
                // If checked, set the button color to yellow
                $(this).closest('button').removeClass('btn-light').addClass('btn-warning');
            } else {
                // If unchecked, reset the button color to light
                $(this).closest('button').removeClass('btn-warning').addClass('btn-light');
            }
        });
    });
    $('.saveOrderBtn').on('click', function() {
        const orderData = {
            unit_purchased: $('#bundleModal #quantity').val(),
            // id: orderId,
            list_price: $('#bundleModal #listPrice').val(),
            msku: $('#bundleModal #msku').val(),
            product_buyer_notes: $('#bundleModal #orderNote').val(),
            min: $('#bundleModal #minPrice').val(),
            max: $('#bundleModal #maxPrice').val(),
            name: $('#bundleModal #orderName').val(),
            asin: $('#bundleModal #orderAsin').val(),
            category: $('#bundleModal #orderCategory').val(),
            supplier: $('#bundleModal #orderSupplier').val(),
            source_url: $('#bundleModal #orderSourceUrl').val(),
            oorder_note: $('#bundleModal #orderProductNote').val(),
            buy_cost: $('#bundleModal #orderCost').val(),
            selling_price: $('#bundleModal #orderSellingPrice').val(),
            bsr: $('#bundleModal #orderBsr').val(),
            promo: $('#bundleModal #orderPromo').val(),
            coupon_code: $('#bundleModal #coupon_code').val(),
            isHazmat: $('#bundleModal #orderIsHazmat').is(':checked'),
            isDisputed: $('#bundleModal #orderIsDisputed').is(':checked')
        };
        // Send data via AJAX to your backend (assuming a URL like '/saveOrder')
        $.ajax({
            url: "{{ url('save-order-data') }}",
            type: 'POST',
            data: JSON.stringify(orderData),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            success: function(response) {
                // Handle success response
                if(response.success){
                    $('#bundleModal').modal('hide');
                    toastr.success('Order and item saved successfully!');
                    window.open(`list/buycostcalculator/${response.id}`, '_blank');
                }else{
                    toastr.success('Order and item saved successfully!');
                }
            },
            error: function(error) {
                // Handle error response
                alert('An error occurred. Please try again.');
            }
        });
    });
    function opneBuyListModal(id){
        openModal(id,1)
        loadBuylistsForModal()
    }
    function loadBuylistsForModal() {
        fetch('{{ url("get-buylists") }}')
        .then(response => response.json())
        .then(buylists => {
        document.querySelectorAll('.buylist-dropdown').forEach(buylistDropdown => {
            buylistDropdown.innerHTML = ''; // Clear previous entries

            let hasTeamBuy = false;
            let activeBuylist = null;
            let activeID = null;
            buylists.forEach(buylist => {
            const isTeamBuylist = buylist.name === 'Team Buylist';
            if (isTeamBuylist) hasTeamBuy = true;
                // Create a dropdown item for each buylist
                const buylistItem = document.createElement('li');
                buylistItem.innerHTML = `
                <a class="dropdown-item buylist-option" href="#" data-buylist-name="${buylist.name}" data-buylistSelect-id="${buylist.id}">
                Add to ${buylist.name}
                </a>`;
                buylistDropdown.appendChild(buylistItem);
            });        
            // Set default selected buylist to the active one or "Team Buylist" if it exists
            const defaultBuylist = activeBuylist || (hasTeamBuy ? 'Team Buylist' : buylists[0]?.name);
            activeID = activeID ||  buylists[0]?.id;
            if(defaultBuylist != 'Team Buylish'){
                $('#buyId').val(activeID)
            }
            selectDefaultBuylistModal(buylistDropdown.closest('.buylist-group'), defaultBuylist);
        });

        // Initialize click handlers for each dropdown item
        initializeBuylistClickHandlerModal();
        })
        .catch(error => {
            console.error('Error fetching buylists:', error);
        });
    }

    function selectDefaultBuylistModal(buylistGroup, buylistName) {
        const buylistButton = buylistGroup.querySelector('.buylist-button');
        if (buylistButton) {
            buylistButton.textContent = 'Add to '+ buylistName;
        }
    }

    function initializeBuylistClickHandlerModal() {
        document.querySelectorAll('.buylist-option').forEach(item => {
            item.addEventListener('click', function (e) {
            e.preventDefault();
            const buylistName = this.getAttribute('data-buylist-name');
            const buylistSelectId = this.getAttribute('data-buylistSelect-id');
            console.log(buylistSelectId);
            $('#buyId').val(buylistSelectId)
            
            // Remove 'active' class from all options and add it to the selected one
            document.querySelectorAll('.buylist-option').forEach(option => {
                option.classList.remove('active');
            });
            this.classList.add('active');

            // Update the text for all .buylist-button elements within the modal
            document.querySelectorAll('.buylist-button').forEach(buylistButton => {
                buylistButton.textContent = 'Add to ' + buylistName; // Update button text for each instance
            });
            });
        });
    }
    function saveBuylistData() {
        
        const buylistId = $('#buyId').val();
        const buyListLeadData = {
            unit_purchased: $('#buyListLeadModal #quantity').val(),
            buylist_id: buylistId, 
            list_price: $('#buyListLeadModal #listPrice').val(),
            msku: $('#buyListLeadModal #msku').val(),
            product_buyer_notes: $('#buyListLeadModal #orderNote').val(),
            min: $('#buyListLeadModal #minPrice').val(),
            max: $('#buyListLeadModal #maxPrice').val(),
            name: $('#buyListLeadModal #orderName').val(),
            asin: $('#buyListLeadModal #orderAsin').val(),
            category: $('#buyListLeadModal #orderCategory').val(),
            supplier: $('#buyListLeadModal #orderSupplier').val(),
            source_url: $('#buyListLeadModal #orderSourceUrl').val(),
            order_note: $('#buyListLeadModal #orderProductNote').val(),
            buy_cost: $('#buyListLeadModal #orderCost').val(),
            selling_price: $('#buyListLeadModal #orderSellingPrice').val(),
            net_profit: $('#orderNetprofit').val(),
            bsr: $('#buyListLeadModal #orderBsr').val(),
            promo: $('#buyListLeadModal #orderPromo').val(),
            coupon_code: $('#buyListLeadModal #coupon_code').val(),
            is_hazmat: $('#buyListLeadModal #orderIsHazmat').is(':checked'),
            is_disputed: $('#buyListLeadModal #orderIsDisputed').is(':checked'),
            quantity: $('#buyListLeadModal #orderQuantity').is(':checked')
        };
        // Send the data to the backend
        $.ajax({
            url: "{{ url('save-buylist-data') }}",  // Replace with the correct URL
            type: 'POST',
            data: buyListLeadData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            success: function(response) {
                toastr.success('Buylist lead added successfully!');
                $('#buyListLeadModal').modal('hide');
                // loadBuylistData(buylistId)
                // Optional: Refresh or update the page based on response
            },
            error: function(xhr) {
                console.error('Error saving data:', xhr.responseText);
            }
        });
    }
    function toggleBundle(leadId, show) {
        const bundleRow = document.getElementById(`bundle_row_${leadId}`);
        const bundleShowIcon = document.getElementById(`bundleShow_${leadId}`);
        const bundleHideIcon = document.getElementById(`bundleHide_${leadId}`);
        
        if (show) {
            bundleRow.style.display = 'flex';      // Show the row
            bundleShowIcon.style.display = 'none'; // Hide the "+" icon
            bundleHideIcon.style.display = 'inline'; // Show the "-" icon
        } else {
            bundleRow.style.display = 'none';      // Hide the row
            bundleShowIcon.style.display = 'inline'; // Show the "+" icon
            bundleHideIcon.style.display = 'none'; // Hide the "-" icon
        }
    }
    function getSoruces() {
        $.ajax({
            url: "{{ url('sources') }}",
            type: "GET",
            async: false,
            success: function(data) {
                let html = ``;
                // Append the checkboxes dynamically
                $.each(data.data, function(index, value) {
                    html += `
                        <li class="list-unstyled me-2">
                            <div class="form-check p-1">
                                <input class="form-check-input soruce-checkbox" checked type="checkbox" value="${value.id}" id="soruce_${value.id}" onchange="updateToggleButton()">
                                <label class="form-check-label me-2" for="soruce_${value.id}">
                                    ${value.list_name}
                                </label>
                            </div>
                        </li>`;
                });

                // Replace the list content
                $('#sorucesUl').empty();
                $('#sorucesUl').append(html);

                // Reset the button state
                updateToggleButton();
            }
        });
    }
    function updateToggleButton() {
        const totalCheckboxes = $('.soruce-checkbox').length;
        const checkedCheckboxes = $('.soruce-checkbox:checked').length;

        const button = $('#selectToggleButton');

        if (checkedCheckboxes === totalCheckboxes && totalCheckboxes > 0) {
            button.text('Deselect All');
            button.off('click').on('click', function() {
                $('.soruce-checkbox').prop('checked', false);
                updateToggleButton();
            });
        } else {
            button.text('Select All');
            button.off('click').on('click', function() {
                $('.soruce-checkbox').prop('checked', true);
                updateToggleButton();
            });
        }
    }
    function calculateProfit() {
        // Get values from inputs
        var sellPrice = parseFloat($('#sell_price').val()) || 0;
        var cost = parseFloat($('#cost').val()) || 0;

        // Calculate profit
        var profit = sellPrice - cost;

        // Set the net profit value
        $('#net_profit').val(profit.toFixed(2)).trigger('change');
        calculateProfitAndROI()
    }

    // Attach event listeners to both inputs
    $('#sell_price, #cost').on('input', calculateProfit);
    function calculateProfitAndROI() {
        // Get values from inputs
        var net_profit = parseFloat($('#net_profit').val()) || 0;
        var cost = parseFloat($('#cost').val()) || 0;
        // Calculate net profit
        var profit = net_profit;
        // $('#net_profit').val(profit.toFixed(2));
        // Calculate ROI
        var roi = cost > 0 ? (profit / cost) : 0;
        var display_roi = roi * 100;
        console.log(display_roi)
        $('#item_roi').val(roi.toFixed(2));
        $('#roi_display').text(display_roi.toFixed(2) + '%');
    }

    // Attach event listeners to update profit and ROI on input changes
    $('#net_profit').on('input', calculateProfitAndROI);
    // Listen for input on the ROI field
    document.getElementById('item_roi').addEventListener('input', function () {
        // Get the current value of the input
        const roiValue = this.value;
        // Update the ROI display above the input
        document.getElementById('roi_display').textContent = roiValue ? `${parseFloat(roiValue * 100).toFixed(2)}%` : '0.00%';
    });
    // function updateCalculations() {
    //     $('#qty_cost').text(`0.00`);
    //     $('#qty_selling').text(`0.00`);
    //     $('#qty_profit').text(`0.00`);
    //     let $costInput = $("#cost");
    //     let quantity_new = $("#quantity_new").val() || 0;
    //     console.log(quantity_new);
    //     let $sellPriceInput = $("#sell_price");
    //     let $netProfitInput = $("#net_profit");
    //     let cost = parseFloat($costInput.val()) || 0;
    //     let sellPrice = parseFloat($sellPriceInput.val()) || 0;
    //     let netProfit = parseFloat($netProfitInput.val()) || 0;
        
    //     // Update Net Profit input field
    //     // Update display text
    //     $('#qty_cost').text(`$${(cost * quantity_new).toFixed(2)}`);
    //     $('#qty_selling').text(`$${(sellPrice * quantity_new).toFixed(2)}`);
    //     $('#qty_profit').text(`$${(netProfit * quantity_new).toFixed(2)}`);
    // }
    function updateCalculations() {
        let quantity_new = parseInt($("#quantity_new").val()) || 0;
        let cost = parseFloat($("#cost").val()) || 0;
        let sellPrice = parseFloat($("#sell_price").val()) || 0;
        let netProfit = parseFloat($("#net_profit").val()) || 0;

        let totalItemCost = cost * quantity_new;
        let totalItemSelling = sellPrice * quantity_new;
        let itemProfit = netProfit * quantity_new;
        let itemProfitPerPiece = itemProfit / (quantity_new || 1);

        // Prepare the table HTML
        let tableHtml = `
            <div class="card-body">
                <div class="summary-box">
                    <table class="table table-bordered text-center">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Qty</th>
                                <th>Selling Price</th>
                                <th>Cost Price</th>
                                <th>Gross Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Total</strong></td>
                                <td><strong>${quantity_new}</strong></td>
                                <td><span class="total_selling_price">$${totalItemSelling.toFixed(2)}</span></td>
                                <td><span class="total_cost_price">$${totalItemCost.toFixed(2)}</span></td>
                                <td><span class="total_gross_profit">$${itemProfit.toFixed(2)}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Per Pcs</strong></td>
                                <td><strong></strong></td>
                                <td><span class="perpcs_selling_price">$${sellPrice.toFixed(2)}</span></td>
                                <td><span class="perpcs_cost_price">$${cost.toFixed(2)}</span></td>
                                <td><span class="perpcs_gross_profit">$${itemProfitPerPiece.toFixed(2)}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        // Append or Replace inside a container
        $('#summaryBox').html(tableHtml);  // ✅ Make sure you have <div id="summaryBox"></div> in your HTML
    }
    $('#quantity_new').on('input',function(){
        updateCalculations()
    })
    $('#sell_price').on('input',function(){
        updateCalculations()
    })
    $('#cost').on('input',function(){
        updateCalculations()
    })
    function updateOrderCalculations() {
        $('#Orderqty_cost').text(`0.00`);
        $('#Orderqty_selling').text(`0.00`);
        $('#Orderqty_profit').text(`0.00`);

        let orderCost = parseFloat($("#orderCost").val()) || 0;
        let orderSellingPrice = parseFloat($("#orderSellingPrice").val()) || 0;
        let orderNetProfit = parseFloat($("#orderNetprofit").val()) || 0;
        let orderQuantity = parseInt($("#orderQuantity").val()) || 1;
        appendTotalHtl(orderCost,orderQuantity,orderNetProfit,orderSellingPrice)
        // Calculate values and update display
        // $('#Orderqty_cost').text(`$${(orderCost * orderQuantity).toFixed(2)}`);
        // $('#Orderqty_selling').text(`$${(orderSellingPrice * orderQuantity).toFixed(2)}`);
        // $('#Orderqty_profit').text(`$${(orderNetProfit * orderQuantity).toFixed(2)}`);
    }

    // Attach event listeners to update calculations on input change
    $('#orderQuantity, #orderCost, #orderSellingPrice, #orderNetprofit').on('input', function () {
        updateOrderCalculations();
    });
    function appendTotalHtl(cost,quantity_new,netProfit,sellPrice){
        
        let totalItemCost = cost * quantity_new;
        let totalItemSelling = sellPrice * quantity_new;
        let itemProfit = netProfit * quantity_new;
        let itemProfitPerPiece = itemProfit / (quantity_new || 1);
        // Prepare the table HTML
        let tableHtml = `
            <div class="card-body">
                <div class="summary-box">
                    <table class="table table-bordered text-center">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Qty</th>
                                <th>Selling Price</th>
                                <th>Cost Price</th>
                                <th>Gross Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Total</strong></td>
                                <td><strong>${quantity_new}</strong></td>
                                <td><span class="total_selling_price">$${totalItemSelling.toFixed(2)}</span></td>
                                <td><span class="total_cost_price">$${totalItemCost.toFixed(2)}</span></td>
                                <td><span class="total_gross_profit">$${itemProfit.toFixed(2)}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Per Pcs</strong></td>
                                <td><strong></strong></td>
                                <td><span class="perpcs_selling_price">$${sellPrice}</span></td>
                                <td><span class="perpcs_cost_price">$${cost.toFixed(2)}</span></td>
                                <td><span class="perpcs_gross_profit">$${itemProfitPerPiece.toFixed(2)}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>`;
        // Append or Replace inside a container
        $('.summary-box').html(tableHtml);  // ✅ Make sure you have <div id="summaryBox"></div> in your HTML
    }

</script>
