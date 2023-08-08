@extends('template')

@section('css')
<link rel="stylesheet" href="{{asset('/')}}css/vendor/select2.min.css" />
<link rel="stylesheet" href="{{asset('/')}}css/vendor/select2-bootstrap4.min.css" />
@endsection
@section('content')


<!-- Form Row Start -->
<!-- Text Content Start -->
<section class="scroll-section" id="textContent">
    <div class="card mb-5">
        <div class="card-body d-flex flex-column">
            <h3 class="card-title mb-4">Statistik Kuisioner</h3>
            <select class="form-select" id="bagian">
                <option value="">Pilih Bagian</option>
                @foreach ($bagian as $item)
                <option value="{{$item->id}}">{{$item->step_kode ." - ". $item->step_nama}}</option>
                @endforeach
            </select>
            <div id="show-pertanyaan">

            </div>
        </div>
    </div>
</section>
<!-- Text Content End -->
<section class="scroll-section" id="formRow" style="display:none">
    <div class="card mb-5">
        <div class="card-body">
            <h4>Filter</h4>
            <div class="row mb-5">

                <div class="col-md-3">
                    <select class="form-select" id="fakultas">
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="prodi">
                    </select>
                </div>
                <div class="col-md-3">
                    <select multiple="multiple" class="form-select" id="tahun-lulus">
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-warning" id="filter">Filter</button>
                </div>

            </div>
            <!-- <h2>Rekap</h2> -->

            <div id="result-show">

            </div>


        </div>
    </div>
</section>


<template id="resultTemplate">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Pilihan Jawaban</th>
                <th scope="col">Total</th>
            </tr>
        </thead>
        <tbody id="show-data-table">

        </tbody>
    </table>
    <h2 class="mt-5">Diagram</h2>
    <div>
        <label class="form-label d-block">Pilih Diagram</label>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="diagram" id="pie" value="pie" />
            <label class="form-check-label" for="pie">Pie</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="diagram" id="bar" value="bar" />
            <label class="form-check-label" for="bar">Bar</label>
        </div>
        <div class="form-check form-check-inline">
            <button class="btn btn-primary" id="terapkan">Terapkan</button>
        </div>
    </div>
    <div id="showDiagram" class="col-sm-12" style="height: 500px;"></div>
</template>
<template id="resultAngkaTemplate">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th scope="col">No</th>
                <th scope="col">Item</th>
                <th scope="col">Hasil</th>
            </tr>
        </thead>
        <tbody id="show-data-hitung">
        </tbody>
    </table>
</template>

<!-- Form Row End -->

@endsection
@section('js')
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="{{asset('/')}}js/vendor/select2.full.min.js"></script>
<script src="{{asset('/')}}js/forms/controls.select2.js"></script>
<script>
    let bagian = document.querySelector("#bagian")

    bagian.addEventListener('change', async function() {
        document.querySelector("#formRow").style.display = 'none'
        let url = "{{route('admin.get.pertanyaan',':bagianId')}}"
        url = url.replace(':bagianId', `${bagian.options[bagian.selectedIndex].value}`)
        response = await fetch(url)
        responseMessage = await response.json()
        console.log(responseMessage);
        let showPertanyaan = document.querySelector("#show-pertanyaan")
        showPertanyaan.innerHTML = ""

        let select = document.createElement('select');
        select.className = "form-select mt-2"
        select.id = "pertanyaan"

        let option = document.createElement('option')
        option.innerText = "Pilih Pertanyaan"
        option.value = ""

        select.appendChild(option)

        responseMessage.forEach(function(data, i) {
            option = document.createElement('option')
            option.innerText = data.pertanyaan
            option.value = data.id
            if (data.text_properties != null) {
                option.setAttribute('data-jenis', data.text_properties.jenis)
            } else {
                option.setAttribute('data-jenis', '-')
            }
            select.appendChild(option)
        });
        showPertanyaan.appendChild(select)

        let pertanyaan = document.querySelector("#pertanyaan")
        const resultShow = document.querySelector("#result-show")

        pertanyaan.addEventListener("change", async function() {
            if (pertanyaan.options[pertanyaan.selectedIndex].value == "") {
                document.querySelector("#formRow").style.display = 'none'
                showDataTable.innerHTML = ""
                return
            }

            if (pertanyaan.options[pertanyaan.selectedIndex].dataset.jenis == "text-biasa") {
                resultShow.innerHTML = ""

            } else if (pertanyaan.options[pertanyaan.selectedIndex].dataset.jenis != "text-angka" && pertanyaan.options[pertanyaan.selectedIndex].dataset.jenis != "text-desimal") {
                resultShow.innerHTML = ""
                dataForm = new FormData()
                dataForm.append('pertanyaanId', pertanyaan.options[pertanyaan.selectedIndex].value)
                dataForm.append('usersId', "awal")
                dataForm.append('filter', "-")
                url = "{{route('admin.get.count.pertanyaan')}}"
                response = await fetch(url, {
                    method: "POST",
                    body: dataForm
                })
                responseMessage = await response.json()
                // console.log(pertanyaan.options[pertanyaan.selectedIndex].value);
                const resultTemplate = document.querySelector("#resultTemplate")
                const result = resultTemplate.content.cloneNode(true);
                resultShow.appendChild(result)
                let showDataTable = document.querySelector("#show-data-table")
                showDataTable.innerHTML = ""

                let fragment = document.createDocumentFragment();
                responseMessage.dataPertanyaan[0].jawaban_jenis.forEach(function(data, i) {
                    let tr = document.createElement('tr');
                    let nomor = document.createElement('td');
                    nomor.innerText = i + 1
                    let pilihanJawaban = document.createElement('td');
                    pilihanJawaban.innerText = data.pilihan_jawaban
                    let total = document.createElement('td');
                    total.innerText = data.total
                    tr.appendChild(nomor)
                    tr.appendChild(pilihanJawaban)
                    tr.appendChild(total)
                    fragment.appendChild(tr);
                });
                showDataTable.appendChild(fragment)
                let daftarJawaban = responseMessage.dataPertanyaan[0].jawaban_jenis
                google.charts.load('current', {
                    'packages': ['corechart', 'bar']
                });
                google.charts.setOnLoadCallback(drawChart);

                function drawChart() {
                    let jawaban = [
                        ['Task', pertanyaan.options[pertanyaan.selectedIndex].innerText]
                    ]
                    daftarJawaban.map(function(data) {
                        jawaban.push([data.pilihan_jawaban, data.total])
                    });
                    console.log(jawaban);
                    var data = google.visualization.arrayToDataTable(
                        jawaban
                    );
                    var options = {
                        title: pertanyaan.options[pertanyaan.selectedIndex].innerText,
                        sliceVisibilityThreshold: 0,
                        pieHole: 0.4,
                    };

                    document.querySelector("#formRow").style.display = 'block'
                    document.getElementById('showDiagram').innerHTML = ""
                    document.querySelector("#terapkan").addEventListener("click", function() {
                        let diagram = document.querySelector('input[name="diagram"]:checked').value;
                        var chart
                        if (diagram == "pie")
                            chart = new google.visualization.PieChart(document.getElementById('showDiagram'));
                        if (diagram == "bar")
                            chart = new google.visualization.BarChart(document.getElementById('showDiagram'));
                        chart.innerHTML = ""
                        chart.draw(data, options);
                    });
                }
            } else {
                resultShow.innerHTML = ""
                const resultAngkaTemplate = document.querySelector("#resultAngkaTemplate")
                const result = resultAngkaTemplate.content.cloneNode(true);
                resultShow.appendChild(result)
                const showDataHitung = document.querySelector("#show-data-hitung");
                showDataHitung.innerHTML = ""
                document.querySelector("#formRow").style.display = 'block'

                dataForm = new FormData()
                dataForm.append('pertanyaanId', pertanyaan.options[pertanyaan.selectedIndex].value)
                dataForm.append('usersId', [])
                url = "{{route('admin.get.angka.result')}}"
                response = await fetch(url, {
                    method: "POST",
                    body: dataForm
                })
                responseMessage = await response.json()
                addDataTable(1, "Total", responseMessage.dataPertanyaan[0].total)
                addDataTable(2, "Rata - Rata", responseMessage.dataPertanyaan[0].rata)
                addDataTable(3, "Maksimal", responseMessage.dataPertanyaan[0].max)
                addDataTable(4, "Minimal", responseMessage.dataPertanyaan[0].min)

                function addDataTable(urut, jenisnya, hasil) {
                    let fragment = document.createDocumentFragment();
                    let tr = document.createElement('tr');
                    let nomor = document.createElement('td');
                    nomor.innerText = urut
                    let jenis = document.createElement('td');
                    jenis.innerText = jenisnya
                    let total = document.createElement('td');
                    total.innerText = hasil
                    tr.appendChild(nomor)
                    tr.appendChild(jenis)
                    tr.appendChild(total)
                    fragment.appendChild(tr);
                    showDataHitung.appendChild(fragment)
                }

            }
            let fakultas = document.querySelector("#fakultas")
            let prodi = document.querySelector("#prodi")
            let tahunLulus = document.querySelector("#tahun-lulus")
            // let prodi 
            setDefaultPilihan(fakultas, 'Fakultas')
            setDefaultPilihan(prodi, 'Prodi')
            setDefaultPilihan(tahunLulus, 'Tahun Lulus')
            response = await fetch('{{route("admin.get.filter")}}')
            responseMessage = await response.json()
            let fragment = document.createDocumentFragment();
            console.log(responseMessage);
            responseMessage.forEach(function(data, i) {
                let option = document.createElement('option');
                option.innerText = data.pilihan_jawaban
                option.value = data.pilihan_jawaban
                fragment.appendChild(option);
            })
            tahunLulus.appendChild(fragment)

            prodi.disabled = true
            getFakultas();
            async function getFakultas() {
                response = await fetch('https://sia.iainkendari.ac.id/data-fakultas')
                responseMessage = await response.json()
                // console.log(responseMessage);
                let fragment = document.createDocumentFragment();
                let showDataTable = document.querySelector("#show-data-table")

                responseMessage.forEach(function(data, i) {
                    let option = document.createElement('option');
                    option.innerText = `${data.singkatan} - Fakultas ${data.nama}`
                    option.value = data.idfakultas
                    fragment.appendChild(option);
                })
                fakultas.appendChild(fragment)
            }
            fakultas.addEventListener('change', async function() {
                setDefaultPilihan(prodi, 'prodi')
                if (fakultas.options[fakultas.selectedIndex].value == "semua" || fakultas.options[fakultas.selectedIndex].value == "")
                    return prodi.disabled = true
                else
                    prodi.disabled = false
                let fragment = document.createDocumentFragment();
                response = await fetch(`https://sia.iainkendari.ac.id/data-prodi/${fakultas.options[fakultas.selectedIndex].value}`)
                responseMessage = await response.json()
                responseMessage.forEach(function(data, i) {
                    let option = document.createElement('option');
                    option.innerText = `${data.singkatan} - ${data.prodi}`
                    option.value = data.idprodi
                    fragment.appendChild(option);
                })
                prodi.appendChild(fragment)
            })
        });
    });
    document.querySelector("#filter").addEventListener('click', async function() {

        const pertanyaanId = pertanyaan.options[pertanyaan.selectedIndex].value
        let dataWhere = {};
        if (fakultas.options[fakultas.selectedIndex].value != "" && fakultas.options[fakultas.selectedIndex].value != "semua")
            dataWhere["tb_mstprodi.idfakultas"] = fakultas.options[fakultas.selectedIndex].value;
        if (prodi.options[prodi.selectedIndex].value != "" && prodi.options[prodi.selectedIndex].value != "semua")
            dataWhere["tb_data.idprodi"] = prodi.options[prodi.selectedIndex].value;

        let dataId = []
        let dataUser = @json($dataUser);
        dataUser.forEach(function(data) {
            dataId.push(data.name);
        });

        dataSend = new FormData()
        dataSend.append('iddata', JSON.stringify(dataId))
        dataSend.append('where', JSON.stringify(dataWhere))
        response = await fetch('https://sia.iainkendari.ac.id/get-id-data', {
            method: "POST",
            body: dataSend
        })
        responseMessage = await response.json()
        // console.log(responseMessage);
        dataId = []
        responseMessage.data.forEach(function(data) {
            dataId.push(data.iddata);
        });
        dataSend = new FormData()
        dataSend.append('iddata', JSON.stringify(dataId))
        response = await fetch('{{route("admin.get.users")}}', {
            method: "POST",
            body: dataSend
        })
        responseMessage = await response.json()
        let usersid = []
        responseMessage.forEach(function(data) {
            usersid.push(data.id)
        })
        console.log(usersid);
        let tahunLulus = document.querySelector("#tahun-lulus")

        dataSend = new FormData()
        dataSend.append('pertanyaanId', pertanyaanId)
        dataSend.append('usersId', JSON.stringify(usersid))
        if (tahunLulus.options[tahunLulus.selectedIndex].value == "" || tahunLulus.options[tahunLulus.selectedIndex].value == "semua") {

            dataSend.append('filter', '-')
        } else {
            var selected = [];
            for (var option of document.getElementById('tahun-lulus').options) {
                if (option.selected) {
                    selected.push(option.value);
                }
            }
            console.log(selected);
            // return;
            dataSend.append('filter', JSON.stringify(selected))
        }


        //mulai dari sini bedami untuk yang angka

        if (pertanyaan.options[pertanyaan.selectedIndex].dataset.jenis == "-") {
            url = "{{route('admin.get.count.pertanyaan')}}"
            response = await fetch(url, {
                method: "POST",
                body: dataSend
            })
            responseMessage = await response.json()
            console.log(responseMessage);

            showDataTable = document.querySelector("#show-data-table")
            showDataTable.innerHTML = ""
            fragment = document.createDocumentFragment();
            responseMessage.dataPertanyaan[0].jawaban_jenis.forEach(function(data, i) {
                let tr = document.createElement('tr');
                let nomor = document.createElement('td');
                nomor.innerText = i + 1
                let pilihanJawaban = document.createElement('td');
                pilihanJawaban.innerText = data.pilihan_jawaban
                let total = document.createElement('td');
                total.innerText = data.total
                tr.appendChild(nomor)
                tr.appendChild(pilihanJawaban)
                tr.appendChild(total)
                fragment.appendChild(tr);
            });
            showDataTable.appendChild(fragment)
            let daftarJawaban = responseMessage.dataPertanyaan[0].jawaban_jenis
            google.charts.load('current', {
                'packages': ['corechart', 'bar']
            });
            google.charts.setOnLoadCallback(drawChart);

            function drawChart() {
                let jawaban = [
                    ['Task', pertanyaan.options[pertanyaan.selectedIndex].innerText]
                ]
                daftarJawaban.map(function(data) {
                    jawaban.push([data.pilihan_jawaban, data.total])
                });
                console.log(jawaban);
                var data = google.visualization.arrayToDataTable(
                    jawaban
                );
                var options = {
                    title: pertanyaan.options[pertanyaan.selectedIndex].innerText,
                    sliceVisibilityThreshold: 0,
                    pieHole: 0.4,
                };

                document.querySelector("#formRow").style.display = 'block'
                document.getElementById('showDiagram').innerHTML = ""
                document.querySelector("#terapkan").addEventListener("click", function() {
                    let diagram = document.querySelector('input[name="diagram"]:checked').value;
                    var chart
                    if (diagram == "pie")
                        chart = new google.visualization.PieChart(document.getElementById('showDiagram'));
                    if (diagram == "bar")
                        chart = new google.visualization.BarChart(document.getElementById('showDiagram'));
                    chart.innerHTML = ""
                    chart.draw(data, options);
                });
            }
        } else {
            const resultShow = document.querySelector("#result-show")

            resultShow.innerHTML = ""
            const resultAngkaTemplate = document.querySelector("#resultAngkaTemplate")
            const result = resultAngkaTemplate.content.cloneNode(true);
            resultShow.appendChild(result)
            const showDataHitung = document.querySelector("#show-data-hitung");
            showDataHitung.innerHTML = ""
            document.querySelector("#formRow").style.display = 'block'

            url = "{{route('admin.get.angka.result')}}"
            response = await fetch(url, {
                method: "POST",
                body: dataSend
            })
            responseMessage = await response.json()
            console.log(responseMessage);
            addDataTable(1, "Total", responseMessage.dataPertanyaan[0].total)
            addDataTable(2, "Rata - Rata", responseMessage.dataPertanyaan[0].rata)
            addDataTable(3, "Maksimal", responseMessage.dataPertanyaan[0].max)
            addDataTable(4, "Minimal", responseMessage.dataPertanyaan[0].min)

            function addDataTable(urut, jenisnya, hasil) {
                let fragment = document.createDocumentFragment();
                let tr = document.createElement('tr');
                let nomor = document.createElement('td');
                nomor.innerText = urut
                let jenis = document.createElement('td');
                jenis.innerText = jenisnya
                let total = document.createElement('td');
                total.innerText = hasil
                tr.appendChild(nomor)
                tr.appendChild(jenis)
                tr.appendChild(total)
                fragment.appendChild(tr);
                showDataHitung.appendChild(fragment)
            }
        }
    });

    function setDefaultPilihan(pilihan, text) {
        pilihan.innerHTML = ""
        let optionPilih = document.createElement('option');
        optionPilih.innerText = `Pilih ${text}`
        optionPilih.value = ""
        optionPilih.selected = true
        pilihan.appendChild(optionPilih)
        let optionSemua = document.createElement('option');
        optionSemua.innerText = `Semua ${text}`
        optionSemua.value = "semua"
        pilihan.appendChild(optionSemua)
    }

    function setStatistikData() {

    }
</script>
@endsection