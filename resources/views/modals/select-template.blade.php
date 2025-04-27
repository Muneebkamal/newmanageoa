<!-- Modal Structure -->
<div class="modal fade" id="templateModal" tabindex="-1" aria-labelledby="templateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="templateModalLabel">Manage Templates</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Select a template to view column mapping. If you would like to update your selected template please delete it and create a new template.</p>
                <p>Note: there is a limit of ten templates per team. If this limit is reached you will need to remove templates in order to add new ones.</p>
                
                <label for="templateSelect">Select Template:</label>
                <select id="templateSelect" class="form-select mb-3">
                    <!-- Options will be populated dynamically -->
                </select>

                <div id="templateData" class="mb-3">
                    <!-- Column mapping will be displayed here -->
                </div>

                <button id="deleteTemplateBtn" class="btn btn-danger d-none">Delete Template</button>
            </div>
        </div>
    </div>
</div>
