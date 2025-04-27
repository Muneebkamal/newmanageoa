 <!-- Modal Structure -->
  <div class="modal fade" id="addAttachmentModal" tabindex="-1" role="dialog" aria-labelledby="addAttachmentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addAttachmentModalLabel">Add Attachment</h5>
          <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="dropzone" id="imageDropzone">
            <p>Drag & Drop images here or click to upload</p>
            <input type="file" accept="image/*" id="fileInput" multiple style="display:none;">
          </div>
          <div class="col-sm-12" id="fileListContainer">
            <!-- Selected files will be displayed here -->
            <ul id="fileList" class="list-group mt-2"></ul>
          </div>
          <div class="col-sm-12">
            <div class="form-group row">
              <div class="col-md-12">
                <label for="createDisplayName" class="col-sm-auto col-form-label">Display Name</label>
                <div class="col-sm-auto">
                  <input type="text" id="createDisplayName" placeholder="File Name" class="form-control">
                </div>
              </div>
              <div class="col-md-12">
                <label for="createNote" class="col-sm-auto col-form-label">Note</label>
                <div class="col-sm-auto">
                  <input type="text" id="createNote" placeholder="Add attachment notes" class="form-control">
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveFiles">Save</button>
        </div>
      </div>
    </div>
  </div>
  <style>
    .dropzone {
        border: 2px dashed #007bff;
        border-radius: 5px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .dropzone.hover {
        background-color: #f0f8ff;
    }

  </style>
  