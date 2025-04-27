<!-- Edit File Modal -->
<div class="modal fade" id="editFileModal" tabindex="-1" aria-labelledby="editFileModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editFileModalLabel">Edit File</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <input type="hidden" name="fileID" id="fileID">
          <form id="editFileForm">
            <div class="mb-3">
              <label for="fileName" class="form-label">File Name</label>
              <input type="text" class="form-control" id="fileName" required>
            </div>
            <div class="mb-3">
              <label for="fileNote" class="form-label">File Note</label>
              <textarea class="form-control" id="fileNote" rows="3"></textarea>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="saveFileChanges()">Save Changes</button>
        </div>
      </div>
    </div>
  </div>
  