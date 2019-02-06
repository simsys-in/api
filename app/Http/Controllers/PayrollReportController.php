<?php

namespace App\Http\Controllers;

use Carbon\CarbonPeriod;
use App\Model\Devicelogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollReportController extends Controller
{
    public function attendance_register(Request $request){
        $date_from =date('Y-m-d',strtotime($request->date_from));
        $date_to =  date('Y-m-d',strtotime($request->date_to));
        $period = CarbonPeriod::create($date_from,$date_to);

        // Iterate over the period
        $days =[];
        $noofdays = $period->count();
        $count=0;
        foreach ($period as $date) {
            $count++;
            $days[$count] = $date->format('d D');
        }
        // Convert the period to an array of dates
        //$dates = $period->toArray();

        //-------------
        $time1  = strtotime($request->date_from);
        $time2  = strtotime($request->date_to);

        $my = date('mY', $time2);
        $months[date('n_Y', $time1)]='';

        while($time1 < $time2) {
           $time1 = strtotime(date('Y-m-d', $time1).' +1 month');
           if(date('mY', $time1) != $my && ($time1 < $time2))
              $months[date('n_Y', $time1)] = '';
        }
     
        $months[date('n_Y', $time2)]='';
        //----------------------

        $tables = [];
        foreach ($months as $month=>$val){
        $tables[] = DB::table('devicelogs_'.$month)
            ->select('UserId','LogDate')->toSql()
            ->whereBetween(DB::raw('DATE(l.LogDate)'),[$date_from,$date_to]);
        }
        $table = implode(' UNION ALL ',$tables);

        $employees_presented = DB::table(DB::raw("(".$table.") l"))
        ->select('employees.EmployeeCode','employees.EmployeeName','l.UserId')
        ->leftJoin('employees','employees.EmployeeCode','=','l.UserId')
        ->groupBy(DB::raw('l.UserId'))
        ->get();

        $tables = [];
        foreach ($months as $month=>$val){
        $tables[] = DB::table('devicelogs_'.$month)
            ->select('UserId','LogDate')->toSql();
        }

        foreach ($employees_presented as $employee_info){
            $row = $employee_info;
            $count=0;
            foreach ($period as $date) {
                $count++;
                $date = $date->format('Y-m-d');
                $logs = DB::table(DB::raw("(".$table.") l"))
                ->where(DB::raw('DATE(l.LogDate)'),$date)
                ->where('l.UserId',$employee_info->UserId)
                ->groupBy(DB::raw('l.UserId'))
                ->pluck(DB::raw("GROUP_CONCAT(DATE_FORMAT(l.LogDate,'%h:%i %p') SEPARATOR ' ') AS time_log"))->first();
                $logs= is_null($logs)?'':$logs;
                $row->logs[$count] = $logs;
            }
            $employees[] =  $row;
        }

        $response = [
            'date_from' =>$date_from,
            'date_to' =>$date_to,
            'days'=>$days,
            'noofdays'=>$noofdays,
            'employees' => $employees,
        ];
        return $response;
        //return $request;
    }
}
