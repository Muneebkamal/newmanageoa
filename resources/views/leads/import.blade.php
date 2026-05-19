@extends('layouts.app')
<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-7">

            <div class="card shadow border-0 rounded-4">

                <div class="card-body p-5">

                    <h2 class="mb-4">
                        Import Google Sheet
                    </h2>

                    @if(session('success'))

                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>

                    @endif

                    <form action="{{ route('sources.import') }}"
                          method="POST">

                        @csrf

                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Google Sheet URL
                            </label>

                            <input type="text"
                                   name="sheet_url"
                                   class="form-control form-control-lg"
                                   placeholder="Paste Google Sheet URL"
                                   required>

                        </div>

                        <button class="btn btn-dark btn-lg w-100">
                            Import Sheet
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection