@extends('layouts.app')
@section('content')
    <div style="width:600px;margin:50px auto;">

        <h2>Import Google Sheet</h2>

        <input type="text"
            id="sheet_url"
            placeholder="Paste Google Sheet URL"
            style="width:100%;height:45px;padding:10px;">

        <br><br>

        <button id="importBtn"
                style="height:45px;padding:0 30px;">
            Import
        </button>

        <br><br>

        <div id="response"></div>

    </div>
@endsection
@section('script')
    <script>
        $('#importBtn').click(function(){

            let sheet_url = $('#sheet_url').val();

            $.ajax({
                url: "{{ url('/google-sheet/import') }}",
                type: "POST",
                data: {
                    sheet_url: sheet_url,
                    _token: "{{ csrf_token() }}"
                },
                beforeSend:function(){
                    $('#response').html('Importing...');
                },
                success:function(res){

                    if(res.success){

                        $('#response').html(
                            '<span style="color:green;">'+res.message+'</span>'
                        );

                    }else{

                        $('#response').html(
                            '<span style="color:red;">'+res.message+'</span>'
                        );

                    }

                },
                error:function(){

                    $('#response').html(
                        '<span style="color:red;">Something went wrong</span>'
                    );

                }
            });

        });
    </script>
@endsection