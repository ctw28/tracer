<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Step;
use App\Models\Pertanyaan;
use App\Models\Jawaban;
use App\Models\JawabanJenis;
use App\Models\User;
use App\Models\UserSesi;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    //
    public function index()
    {

        // $data['dataPertanyaan'] = Pertanyaan::with([
        //     'jawabanJenis'
        // ])->where('id', 6)->get();
        // // return Jawaban::where(['pertanyaan_id' => 6])->get();
        // // return $data[0]->jawabanJenis;
        // $data['dataPertanyaan'][0]->jawabanJenis->map(function ($data) {
        //     $total = Jawaban::where(['pertanyaan_id' => 6, 'jawaban' => $data->pilihan_jawaban])->count();
        //     $data->total = $total;
        // });
        // // foreach ($data as $row) {
        // // }
        // // return $data;
        $data['title'] = "Dashboard";
        $data['totalMasukSistem'] = User::where(['user_role_id' => 2])->count();
        // $data['stepData'] = Step::with('stepChild')->whereNull('step_parent')->orderBy('step_urutan', 'ASC')->get();
        // return $data;
        return view('admin.dashboard', $data);
    }
    public function statistik_bagian()
    {


        // return Jawaban::where(['pertanyaan_id' => 6])->get();
        // return $data[0]->jawabanJenis;

        // foreach ($data as $row) {
        // }
        $data['bagian'] = Step::all();
        // return $data;
        $data['title'] = "Statistik Bagian";
        $data['dataUser'] = User::where('user_role_id', 2)->get();

        // $data['stepData'] = Step::with('stepChild')->whereNull('step_parent')->orderBy('step_urutan', 'ASC')->get();
        // return $data;
        return view('admin.statistik-bagian', $data);
    }

    public function filterData()
    {
        return JawabanJenis::where('pertanyaan_id', 2)->get();
    }

    public function getfilteredData(Request $request)
    {
        $users = [];
        if ($request->filter == "-")
            $userTahunAjar = Jawaban::where(['pertanyaan_id' => 2])->get();
        else
            $userTahunAjar = Jawaban::where(['pertanyaan_id' => 2, 'jawaban' => $request->filter])->get();
        foreach ($userTahunAjar as $item) {
            $users[] = $item->user_id;
        }

        $user = User::whereIn('id', $users)->get();
        $users = [];

        foreach ($user as $item) {
            $users[] = $item->name;
        }
        return $users;
    }
    public function statistik_data_alumni()
    {

        $data['bagian'] = Step::all();
        // return $data;
        $data['title'] = "Statistik Data Alumni";
        $data['dataUser'] = User::where('user_role_id', 2)->get();
        return view('admin.statistik-data-alumni', $data);
    }

    public function getPertanyaan($bagianId)
    {
        return Pertanyaan::with(['textProperties'])->where(['step_id' => $bagianId])->get();
    }

    public function getAlumniData(Request $request)
    {
        $data = collect(json_decode($request->data));
        $data->map(function ($data) {
            $user = User::where('name', $data->iddata)->first();
            if ($user->created_at != null)
                $data->tanggal_login = \Carbon\Carbon::parse($user->created_at)->format('d M Y');
            else
                $data->tanggal_login = "";
        });
        return $data;
    }
    public function getCountJawaban(Request $request)
    {
        // return $request->all();
        $pertanyaanId = $request->pertanyaanId;
        $usersId = json_decode($request->usersId);
        $tahunId = json_decode($request->filter);
        $users = [];
        if ($request->filter == "-") {
            $users = $usersId;
        } else {
            $userTahunAjar = Jawaban::where(['pertanyaan_id' => 2])->whereIn('jawaban', $tahunId)->whereIn('user_id', $usersId)->get();

            // $userTahunAjar = Jawaban::where(['pertanyaan_id' => 2, 'jawaban' => $request->filter])->whereIn('user_id', $usersId)->get();
            foreach ($userTahunAjar as $item) {
                $users[] = $item->user_id;
            }
        }
        // $userTahunAjar->map(function ($user) use ($users) {
        // });
        // $users["aa"] = "mantap";
        // return $users;
        // if ($usersId == null)
        //     $usersId = [];
        $data['dataPertanyaan'] = Pertanyaan::with([
            'jawabanJenis'
        ])->where('id', $pertanyaanId)->get();
        $data['dataPertanyaan'][0]->jawabanJenis->map(function ($data) use ($pertanyaanId, $users) {
            if ($users == null)
                $total = 0;
            // $total = Jawaban::where(['pertanyaan_id' => $pertanyaanId, 'jawaban' => $data->pilihan_jawaban])->count();
            else
                $total = Jawaban::where(['pertanyaan_id' => $pertanyaanId, 'jawaban' => $data->pilihan_jawaban])
                    ->whereIn('user_id', $users)->count();
            $data->total = $total;
        });
        $data['asdfa'] = $request->all();
        $data['user'] = $users;
        return $data;
    }

    public function getTahunMengisi(Request $request)
    {
        $usersId = json_decode($request->usersId);
        $userSesi = UserSesi::select(DB::raw('year(sesi_tanggal) as tahun, count(*) as total'))
            ->groupBy(DB::raw('year(sesi_tanggal)'))
            ->whereIn('user_id', $usersId)
            ->get();
        return $userSesi;
    }

    public function dataAlumni()
    {
        $data['title'] = "Data Alumni";
        $data['dataUser'] = User::where('user_role_id', 2)->get();
        // $data['bagian'] = Step::all();

        return view('admin.data-alumni', $data);
    }

    public function getUsers(Request $request)
    {
        // return $request->all();
        $dataUser = User::where('user_role_id', 2)->whereIn('name', json_decode($request->iddata))->get();
        return $dataUser;
    }

    public function getAngkaResult(Request $request)
    {
        $pertanyaanId = $request->pertanyaanId;
        $usersId = json_decode($request->usersId);
        if ($usersId == null) {
            $data['dataPertanyaan'] = Pertanyaan::with('jawaban')->where('id', $pertanyaanId)->get();
        } else {
            $data['dataPertanyaan'] = Pertanyaan::with(['jawaban' => function ($jawaban) use ($usersId) {
                $jawaban->whereIn('user_id', $usersId);
            }])->where('id', $pertanyaanId)->get();
        }
        $data['dataPertanyaan']->map(function ($data) use ($usersId) {
            if ($usersId == null) {
                $data->total = 0;
                $data->rata = 0;
                $data->max = 0;
                $data->min = 0;
                return;
            }
            $total = $data->jawaban->reduce(function ($tot, $data) {
                return $tot + $data->jawaban;
            }, 0);
            $angka = [];
            // $data->jawaban->foreach(function ($data) {
            //     $angka[] = $data->jawaban;
            // });
            foreach ($data->jawaban as $jawaban) {
                $angka[] = (float)$jawaban->jawaban;
            }
            $data->total = number_format($total, 0, ',', '.');
            $data->rata = round($total / count($data->jawaban), 2);
            // $data->rata = number_format(round($total / count($data->jawaban), 2), 0, ',', '.');
            $data->max = number_format(max($angka), 0, ',', '.');
            $data->min = number_format(min($angka), 0, ',', '.');
        });
        return $data;
    }

    public function detailJawaban($userId)
    {
        $data['title'] = "Detail Jawaban";

        $user = User::where('name', $userId)->first();
        $data['user'] = $user;
        $data['bagian'] = Step::with(['pertanyaan' => function ($pertanyaan) use ($user) {
            $pertanyaan->with(['jawaban' => function ($jawaban) use ($user) {
                $jawaban->where(['user_id' => $user->id]);
            }]);
        }])->get();
        // return $data;
        return view('admin.data-alumni-jawaban', $data);
    }
}
