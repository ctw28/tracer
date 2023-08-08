<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Step;
use App\Models\Jawaban;
use App\Models\User;
use App\Models\BagianDirect;
use App\Models\JawabanJenis;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\FirstOrLast;
use App\Models\JawabanLainnya;
use App\Models\Pertanyaan;
use App\Models\UserSesi;


class UserController extends Controller
{
    public function index()
    {
        $data['title'] = "Dashboard";
        // return session()->get('userData')->id;
        $data['iddata'] = session('iddata');
        $data['first'] = FirstOrLast::first();
        // return $data;
        return view('user.dashboard', $data);
    }
    //
    public function login()
    {
        return view('user.login');
    }

    public function sesi($iddata)
    {
        // $data = json_decode($request->input('data'), true);
        // $sesi = [
        //     'iddata' => $data['iddata'],
        //     'nim' => $data['nim'],
        //     'nama' => $data['nama'],
        //     'idprodi' => $data['idprodi']
        // ];
        $user =
            [
                'user_role_id' => 2,
                'name' => $iddata,
                'email' => $iddata . "@mail.com",
                'password' => $iddata,
                'created_at' => \Carbon\Carbon::now(),
            ];
        $checkUser = User::where('name', $iddata)->first();
        if ($checkUser == null) {
            $user = DB::table('users')->insert($user);
        } else {
            if ($checkUser->created_at == null) {
                $checkUser->created_at = \Carbon\Carbon::now();
                $checkUser->save();
            }
        }
        // else {
        // }

        // session(['data_alumni', $sesi]);
        // $request->session(['data_alumni' => $sesi]);

        // Session::put('data_alumni', $sesi);

        // session(['data_alumni' => $sesi]);

        session()->put('iddata', $iddata);
        session()->put('userData', User::where('name', $iddata)->first());
        return redirect()->route('user.index');
        // return $request->session('data_alumni');
        // return $data['iddata'];
        // return $user;
    }

    public function logout()
    {
        session()->forget('iddata');

        return redirect()->route('user.login');
    }
    public function showPertanyaan($bagianId)
    {
        // $check = Jawaban::with(['jawabanLainnya'])->where([
        //     'user_id' => session()->get('userData')->id,
        //     'pertanyaan_id' => '1',
        // ])->get();
        // return $check[0]->jawabanLainnya->jawaban;
        $data['title'] = "Kuisioner Alumni";
        $data['iddata'] = session('iddata');

        $data['bagianData'] = Step::with(['pertanyaan' => function ($pertanyaan) {
            $pertanyaan->with(['jawabanJenis', 'textProperties'])->orderBy('pertanyaan_urutan', 'ASC');;
        }, 'bagianDirect'])->where('id', $bagianId)->first();

        $type = "text";
        foreach ($data['bagianData']->pertanyaan as $row) {
            $jawaban = "";
            $required = "";
            if ($row->required == 1)
                $required = "required";
            $dataJawaban = Jawaban::where(['user_id' => session()->get('userData')->id, 'pertanyaan_id' => $row->id])->get();
            $gg[] = $dataJawaban;
            if (count($dataJawaban) > 0) {
                $jawaban = $dataJawaban;
            }
            if ($row->pertanyaan_jenis_jawaban == "Text") {
                if ($row->textProperties->jenis == "text-email")
                    $type = "email";
                else if ($row->textProperties->jenis == "text-angka")
                    $type = "number";
                else if ($row->textProperties->jenis == "text-desimal")
                    $type = "number";
                else if ($row->textProperties->jenis == "text-tanggal")
                    $type = "date";
                else
                    $type = "text";
                $content = '<div class="mb-3 position-relative form-group">';
                $content .= '<label class="form-label">' . $row->pertanyaan_urutan . '. ' . $row->pertanyaan . '</label>';
                if (count($dataJawaban) > 0)
                    $content .= "<input step='any' " . $required . " type='" . $type . "' name='input[" . $row->id . "]' class='form-control' value='" . $jawaban[0]->jawaban . "'>";
                else
                    $content .= "<input step='any' " . $required . "  type='" . $type . "' name='input[" . $row->id . "]' class='form-control' value=''>";
                $content .= '</div>';
                $row->form = $content;
            } else if ($row->pertanyaan_jenis_jawaban == "Text Panjang") {
                $content = '<div class="mb-3 position-relative form-group">';
                $content .= '<label class="form-label">' . $row->pertanyaan_urutan . '. ' . $row->pertanyaan . '</label>';
                if (count($dataJawaban) > 0)
                    $content .= "<textarea " . $required . "  name='input[" . $row->id . "]' class='form-control'>" . $jawaban[0]->jawaban . "</textarea>";
                else
                    $content .= "<textarea " . $required . "  name='input[" . $row->id . "]' class='form-control'></textarea>";

                $content .= '</div>';
                $row->form = $content;
            } else if ($row->pertanyaan_jenis_jawaban == "Lebih Dari Satu Jawaban") {
                $content = '<div class="mb-3 position-relative form-group">';
                $content .= '<label class="form-label">' . $row->pertanyaan_urutan . '. ' . $row->pertanyaan . '</label>';
                foreach ($row->jawabanJenis as $index => $item) {
                    $checked = "";
                    if (count($dataJawaban) > 0) {
                        foreach ($jawaban as $jawab) {
                            if ($jawab->jawaban == $item->pilihan_jawaban) {
                                $checked = "checked";
                                break;
                            }
                        }
                    }
                    $content .= '<div class="form-check">
                    <input class="form-check-input" type="checkbox" name="input[' . $row->id . '][]" id="input' . $index . '" value="' . $item->pilihan_jawaban . '" ' . $checked . '/>
                    <label class="form-check-label" for="input' . $index . '">' . $item->pilihan_jawaban . '</label>
                  </div>';
                }
                if ($row->lainnya == "1") {
                    if (count($dataJawaban) > 0) {
                        foreach ($dataJawaban as $jawab) {
                            $checked = ($jawab->jawaban == "lainnya") ? "checked" : '';
                        }
                    }
                    $content .= '<div class="form-check">
                    <input onclick="showTextInput(event, ' . $row->id . ')" class="form-check-input" type="checkbox" name="input[' . $row->id . '][]" id="input' . $row->id . '" value="lainnya" ' . $checked . '/>
                    <label class="form-check-label" for="input' . $row->id . '">Lainnya</label>
                  </div>';
                    $check = Jawaban::with(['jawabanLainnya'])->where([
                        'user_id' => session()->get('userData')->id,
                        'pertanyaan_id' => $row->id,
                        'jawaban' => 'lainnya',
                    ])->get();
                    if (!empty($check[0]->jawabanLainnya))
                        $content .= "<input required name='lainnya[" . $row->id . "]' id='lainnya_" . $row->id . "' type='text' class='form-control' value='" . $check[0]->jawabanLainnya->jawaban . "'>";
                }
                $content .= '</div>';
                $row->form = $content;
            } else if ($row->pertanyaan_jenis_jawaban == "Select") {
                $content = '<div class="mb-3 position-relative form-group">';
                $content .= '<label class="form-label">' . $row->pertanyaan_urutan . '. ' . $row->pertanyaan . '</label>';
                $content .= '<select onchange="showTextInput(event, ' . $row->id . ')"  ' . $required . '  class="form-select" name="input[' . $row->id . ']" required>';
                $content .= '<option value="">Pilih</option>';
                foreach ($row->jawabanJenis as $index => $item) {
                    $selected = "";
                    if (count($dataJawaban) > 0)
                        $selected = ($jawaban[0]->jawaban == $item->pilihan_jawaban) ? "selected" : '';
                    $content .= '<option value="' . $item->pilihan_jawaban . '" ' . $selected . '>' . $item->pilihan_jawaban . '</option>';
                }
                if ($row->lainnya == "1") {
                    if (count($dataJawaban) > 0)
                        $checked = ($jawaban[0]->jawaban == "lainnya") ? "selected" : '';
                    $content .= '<option value="lainnya" ' . $checked . '>Lainnya</option>';
                    $check = Jawaban::with(['jawabanLainnya'])->where([
                        'user_id' => session()->get('userData')->id,
                        'pertanyaan_id' => $row->id,
                        'jawaban' => 'lainnya',
                    ])->get();
                    if (!empty($check[0]->jawabanLainnya))
                        $content .= "<input required name='lainnya[" . $row->id . "]' id='lainnya_" . $row->id . "' type='text' class='form-control' value='" . $check[0]->jawabanLainnya->jawaban . "'>";
                }
                $content .= '</select>';
                $content .= '</div>';
                $row->form = $content;
            } else if ($row->pertanyaan_jenis_jawaban == "Pilihan") {
                $content = '<div class="mb-3 position-relative form-group">';
                $content .= '<label class="form-label">' . $row->pertanyaan_urutan . '. ' . $row->pertanyaan . '</label>';
                foreach ($row->jawabanJenis as $index => $item) {
                    $checked = '';
                    if (count($dataJawaban) > 0)
                        $checked = ($jawaban[0]->jawaban == $item->pilihan_jawaban) ? "checked" : '';
                    $content .= '<div class="form-check">
                    <input onclick="removeTextInput(event, ' . $row->id . ')" ' . $required . ' class="form-check-input" type="radio" name="input[' . $row->id . ']" id="input' . $row->id . '' . $index . '" value="' . $item->pilihan_jawaban . '" ' . $checked . '/>
                    <label class="form-check-label" for="input' . $row->id . '' . $index . '">' . $item->pilihan_jawaban . '</label>
                  </div>';
                }
                if ($row->lainnya == "1") {
                    if (count($dataJawaban) > 0)
                        $checked = ($jawaban[0]->jawaban == "lainnya") ? "checked" : '';
                    $content .= '<div class="form-check">
                        <input onclick="showTextInput(event, ' . $row->id . ')" class="form-check-input" type="radio" name="input[' . $row->id . ']" id="inputlainnya' . $row->id . '" value="lainnya" ' . $checked . '/>
                        <label class="form-check-label" for="inputlainnya' . $row->id . '">Lainnya</label>
                    </div>';
                    // $check = JawabanLainnya::where('pertanyaan_id', $row->id)->get();
                    $check = Jawaban::with(['jawabanLainnya'])->where([
                        'user_id' => session()->get('userData')->id,
                        'pertanyaan_id' => $row->id,
                        'jawaban' => 'lainnya',
                    ])->get();
                    if (!empty($check[0]->jawabanLainnya))
                        $content .= "<input required name='lainnya[" . $row->id . "]' id='lainnya_" . $row->id . "' type='text' class='form-control' value='" . $check[0]->jawabanLainnya->jawaban . "'>";
                }
                $content .= '</div>';

                $row->form = $content;
            }
        }
        // return $gg;
        $data['akhir'] = false;
        $data['awal'] = false;
        $awal = FirstOrLast::where('step_id_first', $bagianId)->count();
        if ($awal > 0)
            $data['awal'] = true;
        $akhir = FirstOrLast::where('step_id_last', $bagianId)->count();
        if ($akhir > 0)
            $data['akhir'] = true;

        return view('user.show-pertanyaan', $data);
        return $data;
    }

    public function storeJawaban(Request $request, $bagianId)
    {
        return $request->all();
        try {
            if ($request->awal == 1) {
                $userSesi = UserSesi::where(['user_id' => session()->get('userData')->id, 'sesi_status' => "1"])->count();
                if ($userSesi == 0) {
                    UserSesi::updateOrCreate(
                        [
                            'user_id' => session()->get('userData')->id
                        ],
                        [
                            'sesi_tanggal' => \Carbon\Carbon::now(),
                            'sesi_status' => "0"
                        ]
                    );
                }
            } else if ($request->akhir == 1) {
                $userSesi = UserSesi::where('user_id', session()->get('userData')->id)->first();
                $userSesi->sesi_status = "1";
                $userSesi->save();
            }

            foreach ($request->input as $key => $value) {
                if (gettype($value) == "array") {  //ini untuk jawaban yang pilihan lebih dari satu
                    $jawaban = Jawaban::where([
                        'user_id' => session()->get('userData')->id,
                        'pertanyaan_id' => $key
                    ])->delete(); //hapus dulu semua jawaban yang sudah ada dari pertanyaan ini supaya tidak duplikat karena mau diinsert ulang dan jawaban lainnya terhapus memang jg

                    foreach ($value as $row) { // ini diinsertmi semua pilihan2 yang sudah dipilih
                        Jawaban::create( // kenapa nda pakai update or create karena bisa jadi sudah nda sama pilihannya, jadi nda bisa diupdate
                            [
                                'user_id' => session()->get('userData')->id,
                                'pertanyaan_id' => $key,
                                'jawaban' => $row
                            ]
                        );
                    }
                } else { // ini untuk simpan jawaban selain yang bukan pilihan lebih dari satu seperti text biasa, pilihan salah satu, dll
                    $jawaban = Jawaban::updateOrCreate(
                        [
                            'user_id' => session()->get('userData')->id,
                            'pertanyaan_id' => $key
                        ],
                        [
                            'jawaban' => $value
                        ]
                    );
                    JawabanLainnya::where('jawaban_id', $jawaban->id)->delete(); //ini dihapus dulu jawaban lainnya kalau memang dia punya jawaban "lainnnya" nanti diinsert baru lagi
                }
                if (isset($request->lainnya)) { // ini untuk cek apakah ada jawaban "lainnya" yang diisi
                    if (isset($request->lainnya[$key]) && !empty($request->lainnya[$key])) { // cek ada atau tidak yang khusus pertanyaan ini punya jawaban "lainnya"
                        $jawaban = Jawaban::where([
                            'user_id' => session()->get('userData')->id,
                            'pertanyaan_id' => $key,
                            'jawaban' => 'lainnya'
                        ])->first();
                        JawabanLainnya::create(
                            [
                                'jawaban_id' => $jawaban->id,
                                'jawaban' => $request->lainnya[$key]
                            ]
                        );
                    }
                }
            }

            $direct = BagianDirect::where('step_id', $bagianId)->first();
            $akhir = FirstOrLast::where('step_id_last', $bagianId)->count();
            if ($akhir > 0) {
                $data['title'] = "Selesai";
                $data['iddata'] = session('iddata');

                return view('user.selesai', $data);
            }
            // return $direct;
            if ($direct->is_direct_by_jawaban == 0) { //jika tidak direct berdasarkan jawaban 
                return redirect()->route('user.show.pertanyaan', $direct->step_id_direct);
            } else { // jika direct
                foreach ($request->input as $key => $value) {
                    $jawabanJenis = JawabanJenis::with('jawabanRedirect')->where([
                        'pertanyaan_id' => $key,
                        'pilihan_jawaban' => $value
                    ])->first();
                }
                // return $jawabanJenis;
                return redirect()->route('user.show.pertanyaan', $jawabanJenis->jawabanRedirect->step_id_redirect);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
