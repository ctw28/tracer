@extends('user.template-user')

@section('content')

<section class="scroll-section" id="basic">
    <!-- Basic Start -->
    <div class="card mb-5">
        <div class="card-body">
            <h3 class="mb-4">{{$bagianData->step_kode}}. {{$bagianData->step_nama}}</h3>
            <form action="{{route('user.store.jawaban',$bagianData->id)}}" method="post" enctype="multipart/form">
                @csrf
                <input type="hidden" name="step_id" value="{{$bagianData->id}}">
                @foreach ($bagianData->pertanyaan as $tanya)
                {!!$tanya->form!!}
                @endforeach



                @if($akhir==true)
                <button type="button" class="btn btn-dark" onclick="kembali('{{$bagianData->bagianDirect->step_id_direct_back}}')">Kembali</button>
                <button type="submit" class="btn btn-warning" onclick="return confirm('Yakin Selesai?')">Selesai</button>
                @else
                @if($awal==false)
                <button type="button" class="btn btn-dark" onclick="kembali('{{$bagianData->bagianDirect->step_id_direct_back}}')">Kembali</button>
                @endif
                <button type="submit" class="btn btn-primary">Simpan dan Lanjut</button>
                @endif
            </form>
        </div>
    </div>
</section>
<!-- Basic End -->

@endsection

@section('js')
<script>
    async function kembali(bagianId) {
        let urlBack = "{{route('user.show.pertanyaan',':bagianId')}}"
        urlBack = urlBack.replace(':bagianId', bagianId)
        window.location.replace(urlBack);

        // alert(bagianId)
    }

    function removeTextInput(event, pertanyaanId) {
        if (event.target.parentNode.parentNode.contains(document.querySelector("#lainnya_" + pertanyaanId))) {
            document.querySelector("#lainnya_" + pertanyaanId).remove();
        }
    }

    function showTextInput(event, pertanyaanId) {
        // console.log(event.target.parentNode.parentNode);
        if (!event.target.parentNode.parentNode.contains(document.querySelector("#lainnya_" + pertanyaanId))) {
            let input = document.createElement('input');
            input.className = 'form-control'
            input.name = `lainnya[${pertanyaanId}]`
            input.id = `lainnya_${pertanyaanId}`
            // event.target.removeAttribute('onclick')
            event.target.closest('div').after(input);
        }
    }
</script>
@endsection