<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class ActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $authUser = JWTAuth::user();
        $last_activity = DB::table('activities')
            ->where('user_id', $authUser->id)
            ->orderByDesc('id')
            ->value('created_at');

        $last7days_activities = DB::table('activities')
            ->where('user_id', $authUser->id)
            ->whereDate('created_at', '>=', Carbon::parse($last_activity)->subDays(7));

        $total_burned_cal = $last7days_activities->sum('burned_cal');
        $total_score = $last7days_activities->sum('score');
        $total_time = $last7days_activities->sum('time');

        $daily_activity = $last7days_activities->select(DB::raw('DATE(created_at) as date'),  DB::raw('sum(burned_cal) as burned_cal'), DB::raw('sum(time) as time'))
            ->groupBy('date')
            ->orderBy('date', 'desc');

        return response()->json([
            'score' => $total_score,
            'burned_cal' => $total_burned_cal,
            'time' => $total_time,
            'activity' => $daily_activity->get(),
        ], 201);
    }

    public function store(Request $request)
    {
        $authUser = JWTAuth::user();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'score' => 'required|numeric',
            'burned_cal' => 'required|numeric',
            'time' => 'required|numeric',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $activity = Activity::create([
            'user_id' => $authUser->id,
            'name' => $request->name,
            'burned_cal' => $request->burned_cal,
            'score' => $request->score,
            'time' => $request->time,
        ]);
        return response()->json(['message' => 'User successfully registered.', 'activity' => $activity], 201);

    }
}
