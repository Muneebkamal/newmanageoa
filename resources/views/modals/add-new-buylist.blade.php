 <!-- Modal Structure -->
 <div class="modal fade" id="modal-team-create-buylist" tabindex="-1" role="dialog" aria-labelledby="addAttachmentModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modalTeamCreateBuylistLabel">New Buylist</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row justify-content-center">
                    <div class="col-auto">
                        <form class="form-group">
                            <div class="form-group row">
                                <div class="col-12">
                                    You can create up to 7 buylists in addition to the default Team Buylist. Use these to organize your business and better achieve your goals.
                                </div>
                            </div>
                            <div class="form-group row mt-3">
                                <label for="listNameInput" class="col-sm-4 col-form-label">Buylist Name</label>
                                <div class="col-sm-8">
                                    <input type="text" id="listNameInput" placeholder="New Buylist" required class="form-control">
                                    <small id="info-create-name" class="form-text text-muted">No special characters (*!@#$%^&amp;)</small>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="createBuylistButton">Create Buylist</button>
            </div>
        </div>
    </div>
  </div>
  