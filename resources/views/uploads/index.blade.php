@extends('layouts.app')

@section('title', 'My Uploads')

@section('styles')
    <style>
        .lead-menu-active {
            color: #fff;
            background-color: #405189;
        }

        .lead-menu-active:hover {
            color: #fff;
        }

        .lead-menu-hover:hover {
            color: #fff;
            background-color: #405189;
        }

        /* file upload setion dropzone css */
        .dropzone {
            border: 1px dashed black;
            border-radius: 0;
            padding: 20px;
            text-align: center;
            background-color: #fff;
            transition: background-color 0.3s;
        }

        .dropzone:hover {
            background-color: #e9ecef;
        }

        .input-field {
            display: none;
            /* Hide the file input */
        }

        .call-to-action {
            margin-top: 10px;
        }

        .text-success {
            color: #28a745;
        }

        .text-muted {
            color: #6c757d;
        }

        /* Hide the default Dropzone message */
        .dz-message {
            display: none;
            /* Hide the default Dropzone message */
        }
        .buylist_active {
            background-color: #004ea1 !important; /* Adjust color as needed */
            color: #ffffff !important;
        }
    </style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">

            <div class="page-title-right d-flex align-items-center">
                <ol class="breadcrumb m-0 me-3">
                    <li class="breadcrumb-item">
                        <a href="{{ url('my-uploads') }}">
                            <h3 class="mb-0">My Uploads</h3>
                        </a>
                    </li>
                </ol>
            </div>
            <div class="d-flex flex-column flex-sm-row gap-2">
                <select id="sourceFilter" class="form-select me-2" onchange="sourceFindAgain(this)" style="width:auto; min-width:180px;">
                    
                </select>
                <button class="btn btn-outline-primary" type="button" onclick="fileUploadView()">Upload Lead File</button>
                <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal"
                    data-bs-target="#exampleModalScrollable" onclick="formLeadClear()">Add Lead</button>
            </div>

        </div>
    </div>
</div>
    <div class="row">
        <input type="hidden" id="theFilePath" name="theFilePath" value="">
        
        <div class="col-md-12 mt-4">
            <div id="source-alert"></div>
            <div id="leads-alert"></div>

            <div class="d-flex justify-content-center">
                <div id="file-section" class="card w-50">
                    <div class="card-body">
                        <div class="text-center w-100">
                            <h4 style="font-weight: 300;">What would you like to do?</h4>
                        </div>
                        <div class="w-100 d-flex justify-content-center mt-4">
                            <button class="btn btn-primary me-3" type="button" onclick="fileUploadView()">Upload Lead
                                File</button>
                            <button class="btn btn-primary">Manage Leads</button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="table-section" class="card d-none">
                <div class="card-header d-flex align-items-center justify-content-between">
                   <div class=" d-flex align-items-center justify-content-between">
                     <!-- Source Name -->
                     <div id="sourceName" class="me-2"></div>
                    
                     <!-- Source Actions -->
                     <div id="source-action"></div>
                   </div>
                    
                    <!-- Sort Controls -->
                    <div class="team-list-controls-td team-list-controls-sort">
                        <div class="input-group input-group-sm">
                            <!-- Sort Label -->
                            <label for="inputSortSelect" class="col-form-label me-2">Sort By</label>
                            
                            <!-- Sort Field Dropdown -->
                            <select id="inputSortSelect" class="form-control custom-select-sm">
                                <option  value="">Choose...</option>
                                <option value="asin">ASIN</option>
                                <option value="supplier">Supplier</option>
                                <option value="publish_time">Publish Date</option>
                                <option value="created_at" selected="selected">Upload Date</option>
                            </select>
                            
                            <!-- Order Direction Dropdown -->
                            <select id="orderbyinput" class="form-control  custom-select-sm ms-2">
                                <option value="ASC">Oldest to Newest</option>
                                <option value="DESC" selected="selected">Newest to Oldest</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <table id="uploads" class="table table-bordered table-striped align-middle" style="width:100%">
                        <thead>
                            <th scope="col" style="width: 10px;">
                                <div class="d-flex">
                                    <div class="form-check">
                                        <input class="form-check-input fs-15 checkAll" type="checkbox" id="checkAll" value="">
                                    </div>
                                    <i class="mdi mdi-dots-vertical fs-5 ms-2  bulk-action-dropdown" style="display: none;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" style="cursor: pointer" data-bs-toggle="modal"
                                            data-bs-target="#leadSourceModal"  onclick="">
                                            <i class="ri-exchange-line text-primary me-2"></i> Change Lead Source
                                        </a>
                                        <a class="dropdown-item" style="cursor: pointer"  data-bs-toggle="modal"
                                            data-bs-target="#dateModifyModal" onclick="">
                                            <i class="ri-calendar-line text-primary me-2"></i> Modify Date
                                        </a>
                                        <a class="dropdown-item" style="cursor: pointer"  data-bs-toggle="modal"
                                            data-bs-target="#exampleModalScrollable" onclick="">
                                            <i class="ri-pencil-line text-primary me-2"></i>Group into Bundle
                                        </a>
                                        <a class="dropdown-item" style="cursor: pointer"  id="deleteSelected">
                                            <i class="ri-delete-bin-line text-danger me-2"></i>Delete Selected
                                        </a>
                                    </div>
                                </div>
                            </th>
                            <th>Name</th>
                            <th>ASIN</th>
                            <th>Source</th>
                            <th>Category</th>
                            <th>Cost</th>
                            <th>Selling Price</th>
                            <th>Net Profit</th>
                            <th>Quantity
                            <th>Note</th>
                            <th>Publish Date</th>
                        </thead>
                    </table>
                </div>
            </div>
            <input type="hidden" name="selectSource" id="selectSource">

            <div id="upload-file-section" class="d-none">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Nav tabs -->
                                <ul class="nav nav-pills nav-justified mb-3" role="tablist">
                                    <li class="nav-item waves-effect waves-light">
                                        <a class="nav-link active" data-bs-toggle="tab" 
                                            role="tab" href="#pill-justified-home-1">
                                            Upload your CSV
                                        </a>
                                    </li>
                                    <li class="nav-item waves-effect waves-light">
                                        <a class="nav-link" data-bs-toggle="tab" 
                                            role="tab" href="#pill-justified-profile-1" disabled>
                                            Confirm Settings
                                        </a>
                                    </li>
                                    <li class="nav-item waves-effect waves-light">
                                        <a class="nav-link" data-bs-toggle="tab" 
                                            role="tab" href="#pill-justified-messages-1" disabled>
                                            Review/Complete
                                        </a>
                                    </li>
                                </ul>
                                <hr>
                                <!-- Tab panes -->
                                <div class="tab-content text-muted">
                                    <div class="tab-pane active" id="pill-justified-home-1" role="tabpanel">
                                        <div class="d-flex">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <div id="my-dropzone" class="dropzone my-1 h-100 d-flex flex-column align-items-center justify-content-center" style="cursor:pointer !important;">
                                                        <form action="/upload-file" class="dropzone" id="my-dropzone" style="display: none;">
                                                            @csrf
                                                        </form>
                                                        <div id="dropzoneContent" class="text-center">
                                                            <span id="successMessage" class="d-none" style="color: #28a745; font-weight: bold;">File uploaded successfully!</span>
                                                        </div>
                                                        <div id="newMessages" class="text-center" style="cursor:pointer !important;" >
                                                            <i class="ri-file-excel-line fs-1 text-success" style="cursor:pointer !important;"></i>
                                                            <br>
                                                            <strong style="cursor:pointer !important;">Select a CSV file to import</strong>
                                                            <br>
                                                            <span style="cursor:pointer !important;" class="text-muted">or drag and drop it here</span>
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                                <div class="col-md-7">
                                                    <p>You can upload any .csv file (.xlsx will not be accepted) with any
                                                        set of columns as long as it has one record per row. In the next
                                                        step you will be able to match your file's column headers to the
                                                        headers used in Cheddarsoft. You will be able to review and repair
                                                        data before publishing your upload.</p>
                                                    <div class="d-flex align-items-center">
                                                        <div class="mb-3 w-50">
                                                            <label for="">Transfer item List</label>
                                                            <select id="update_source_id" name="update_source_id"
                                                                class="form-select lead-source-list3" required>

                                                            </select>
                                                        </div>
                                                        <span class="mx-3">or</span>
                                                        <button onclick="formClear()" data-bs-toggle="modal"
                                                            data-bs-target="#myModal" type="button"
                                                            class="btn btn-outline-primary mt-2 px-1">Create New Lead
                                                            List
                                                            Source</button>
                                                    </div>
                                                    <small>What lead list would you like to upload these leads to?</small>
                                                    <div class="text-end">
                                                        <button type="button" id="uploadButton" class="btn btn-primary">Upload
                                                            File</button>
                                                        <button disabled class="btn btn-primary d-none" id="uploadNext"
                                                            onclick="nextTab('pill-justified-profile-1')">Next</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="pill-justified-profile-1" role="tabpanel">
                                        <div id="confirm-sett" class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label for="">Lead List Source</label>
                                                    <select name="update_source_id" class="form-select lead-source-list3"
                                                        required>

                                                    </select>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>Select Template</span>
                                                    <button class="btn btn-light p-1" onclick="getTemplatesSelect()">
                                                        <i class="ri-pencil-line text-primary"></i>
                                                    </button>
                                                </div>
                                                <div class="temp-list mb-2" id="templateList">
                                                   
                                                </div>
                                                <div class="create-temp-btn mb-2">
                                                    <button type="button" class="btn btn-outline-primary p-0 p-2"
                                                        onclick="createTempSection()">Create New
                                                        Template</button>
                                                </div>
                                                <div class="btns">
                                                    <button onclick="proceedLead()" class="btn btn-success mb-2 w-100">Upload Data</button>
                                                    <button class="btn btn-outline-primary w-100">Need Assistance</button>
                                                </div>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <h5 class="m-0">Upload Preview</h5>
                                                            <button onclick="proceedLead()" class="btn btn-success">Upload Data</button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12 mt-2">
                                                        <div class="table-responsive">
                                                            <table id="dataTableOld" class="table table-striped">
                                                                <thead></thead>
                                                                <tbody></tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12 text-end">
                                                <button class="btn btn-primary"
                                                    onclick="nextTab('pill-justified-messages-1')">Next</button>
                                            </div>
                                        </div>
                                        <div id="temp-create-section" class="row d-none">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <button type="button" class="btn btn-outline-danger p-2 mb-2"
                                                            onclick="backConfirmSection()">
                                                            <i class="ri-arrow-left-line me-1"></i>Cancel new template
                                                            creation</button>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <h5>Map Your Columns</h5>
                                                        <p class="mb-2">In order to get most out of the Smart Data, we
                                                            need to know which
                                                            of your columns match up with ours. This will enable you to
                                                            search, sort, and filter your data using the same tools we build
                                                            in the Smart Data tool.</p>
                                                        <ul>
                                                            <li>
                                                                <p>For each Smart Data field on the left, select the
                                                                    matching column header from your file.</p>
                                                            </li>
                                                            <li>
                                                                <p>Note that <b>Name</b> and <b>ASIN</b> are the only
                                                                    required fields. You
                                                                    can leave the others blank, but we recommend you map as
                                                                    much data as possible to get the most out of Smart Data
                                                                    tools now and in the future.</p>
                                                            </li>
                                                            <li>
                                                                <p>Once you're done, select the Create Template button and
                                                                    the new mapping template will be ready to use.</p>
                                                            </li>
                                                            <li>
                                                                <p><b>Tags!</b> Mapping the tag column allows you to apply
                                                                    tags
                                                                    when uploading. The values in this column are
                                                                    case-sensitive, comma separated, and must match a team
                                                                    tag to be applied. Non-matches do not have tags applied.
                                                                </p>
                                                            </li>
                                                        </ul>
                                                        <hr>
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <div class="card">
                                                                    <div class="card-header">
                                                                        <div class="row">
                                                                            <div class="col-md-5">
                                                                                <span>Smart Data Fields
                                                                                    <i class="ri-question-line"></i>
                                                                                </span>
                                                                            </div>
                                                                            <div class="col-md-1">
                                                                                <i class="ri-arrow-right-line fs-5"></i>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <span>Columns in your file
                                                                                    <i class="ri-question-line"></i>
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="card-body" id="mappingContainer">
                                                                        
                                                                        
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-8">
                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <h6>New Template Preview</h6>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group row mb-1">
                                                                            <label for="nameTemplate"
                                                                                class="col-sm-4 col-form-label">Template
                                                                                Name</label>
                                                                            <div class="col-sm-8">
                                                                                <input type="text" id="nameTemplate" name="nameTemplate"
                                                                                    class="form-control"> <!---->
                                                                                <span class="text-danger" id="error-message"></span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <button id="saveTemplateBtn" class="btn btn-primary" disabled>Create
                                                                            Template</button>
                                                                    </div>
                                                                    <div class="col-md-12">
                                                                        <div class="table-responsive">
                                                                            <table id="dataTableNew" class="table table-striped">
                                                                                <thead></thead> <!-- Header will be populated dynamically -->
                                                                                <tbody></tbody> <!-- Body will be populated dynamically -->
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane" id="pill-justified-messages-1" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <h5 class="m-0">Recent Uploads</h5>
                                                    <div class="btns">
                                                        <button id="showAllSourceLeads" class="btn btn-outline-primary">Show All Source Leads</button>
                                                        <button onclick="fileUploadView()" class="btn btn-outline-primary">Update More Leads</button>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="card">
                                                    <div class="card-header d-flex align-items-center">
                                                        <div id="sourceNameNew" class="me-2"></div>
                                                        <div id="source-actionNew"></div>
                                                        <div><span class="badge bg-success"><span id="cout"></span> Items added successfully!</span></div>
                                                    </div>
                                                    <div class="card-body">
                                                        <table id="uploadsNew" class="table table-bordered table-striped align-middle" style="width:100%">
                                                            <thead>
                                                                <th scope="col" style="width: 10px;">
                                                                    <div class="d-flex">
                                                                        <div class="form-check">
                                                                            <input class="form-check-input fs-15 checkAll" type="checkbox" id="checkAll" value="">
                                                                        </div>
                                                                        <i class="mdi mdi-dots-vertical fs-5 ms-2  bulk-action-dropdown" style="display: none;" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
                                                                        <div class="dropdown-menu">
                                                                            <a style="cursor: pointer"  class="dropdown-item" data-bs-toggle="modal"
                                                                                data-bs-target="#leadSourceModal" onclick="">
                                                                                <i class="ri-exchange-line text-primary me-2"></i> Change Lead Source
                                                                            </a>
                                                                            <a style="cursor: pointer"  class="dropdown-item" data-bs-toggle="modal"
                                                                                data-bs-target="#dateModifyModal" onclick="">
                                                                                <i class="ri-calendar-line text-primary me-2"></i>Modify Date
                                                                            </a>
                                                                            <a style="cursor: pointer"  class="dropdown-item" data-bs-toggle="modal"
                                                                                data-bs-target="#exampleModalScrollable" onclick="">
                                                                                <i class="ri-pencil-line text-primary me-2"></i>Group into Bundle
                                                                            </a>
                                                                            
                                                                            <a style="cursor: pointer"  class="dropdown-item" onclick="" id="deleteSelected">
                                                                                <i class="ri-delete-bin-line text-danger me-2"></i>Delete Selected
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </th>
                                                                <th>Name</th>
                                                                <th>ASIN</th>
                                                                <th>Source</th>
                                                                <th>Category</th>
                                                                <th>Cost</th>
                                                                <th>Selling Price</th>
                                                                <th>Note</th>
                                                                <th>Publish Date</th>
                                                            </thead>
                                                        </table>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end card-body -->
                        </div><!-- end card -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('modals.sources.source-modal')
    @include('modals.select-template')
    @include('modals.leads.add-modal')
    @include('modals.update-date-modal')
    @include('modals.update-source-modal')
@endsection

@section('script')
    @include('uploads.js.source-js')
    @include('leads.js.lead-js')
    @include('leads.js.upload-file-js')
@endsection
