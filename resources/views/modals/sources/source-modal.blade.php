<div id="myModal" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true"
    style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">New Lead List Source</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Group of all your leads based on the source of the leads, e.g. OA Leads Pro, OA
                    Hunt, Tactical Arbitrage, Rabbit Trails, VA's Finds, ect.</p>

                <form id="source-form" method="POST" action="{{ route('source.create') }}">
                    @csrf
                    <input type="hidden" id="edit_id" name="edit_id">
                    <label for="">List Name</label>
                    <input type="text" class="form-control" id="list_name" name="list_name"
                        placeholder="My New List Sorce">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="close-btn" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="create-btn">Create List</button>
                <button type="button" class="btn btn-success" id="update-btn">Update List</button>
            </div>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
