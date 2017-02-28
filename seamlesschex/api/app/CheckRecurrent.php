<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CheckRecurrent extends Model
{
    
	/**
    * getNextRun
    *
    * @desc - get next run
    *  
    * @return 
    *
    */
    public function getNextRun( $runs_every, $time, $weekday, $day, $date, $relative_date)
    {
        //$current = Carbon::now();
		//$dt_recurr = Carbon::parse($relative_date);
		//$nextrun = $dt_recurr->addDays(1);
		//$nextrun  = $nextrun->format('Y-m-d');
		//$dt_recurr->addWeekdays(1);
		$nextrun = "";
        if($runs_every == 'day')
        {
            $nextrun = date('Y-m-d H:i:s', strtotime('+1 day', strtotime($relative_date . ' ' . $time )));
        }
        else if($runs_every == 'week')
        {
            $nextrun = date('Y-m-d H:i:s', strtotime( ' next ' . $weekday . ' ' . $time, strtotime($relative_date)));
        }
        else if($runs_every == 'month')
        {
            $timestamp = strtotime($relative_date . ' ' . $time);
            $cons_date = mktime(date('H', $timestamp), date('i', $timestamp), 0, date('n', strtotime($relative_date)), $day);
            $nextrun = date('Y-m-d H:i:s', strtotime('next month' , $cons_date));
        }
        else if($runs_every == 'year')
        {
            $timestamp = strtotime('next year', strtotime($date));
            if(strtotime($date) > strtotime('+1 day'))// date isn't passed
                $nextrun =  date('Y-m-d H:i:s', strtotime ( $date));
            else
                $nextrun = date('Y-m-d H:i:s',  $timestamp);
        }
		else if($runs_every == 'twoweek')
        {
            $timestamp = strtotime('+2 weeks', strtotime($date));
            $nextrun = date('Y-m-d H:i:s',  $timestamp);
				
        }
        return $nextrun;
    }
	
	/**
    * fetchRecurrentDetails
    *
    * @desc - fetch Recurrent Details
    * @return mixed recurrent details
    *
    */
    public  function fetchRecurrentDetails( $id = null, $check_id = null, $for_cron = false)
    {
        
        /*$sql = "SELECT CR.* FROM check_recurrent CR"
            . " INNER JOIN check_add CA ON CA.id = CR.check_id"
            . " INNER JOIN check_cmpny CC ON CC.id = CA.company_id AND CC.disabled = '0'";
        $where = " WHERE";
        if($id)
            $where .= " id = '" . (int) $id . "'";
        if($check_id)
            $where .= " check_id = '" . (int) $check_id . "'";
        //additional parameters should be included if query from cron
        if($for_cron)
            $where .= " next_run <= NOW() AND fetch_lock = '0' AND how_many > count_run";
        $sql .= $where;
       // echo $sql;
        //$sql = "Select * from check_recurrent";
        $this->execute_query($sql);
        $result = $this->fetchArray();
        /*echo '<pre>';
        print_r($result);
        if($result && !$for_cron) return $result[0];
        return $result;*/
    }
	
	/**
    * editRecurrence
    *
    * @desc - edit Recurrence
    *
    * @return 
    *
    */
    public function editRecurrence($check_id, $runs_every, $time, $weekday, $day, $date, $how_many)
    {
        /*$recurrentDet = $this->fetchRecurrentDetails(null, $check_id);
        $relativeDate = ($recurrentDet['count_run'] > 0) ? date('Y-m-d', strtotime($recurrentDet['last_run'])) : date('Y-m-d');
        $next_run = self::getNextRun($runs_every, $time, $weekday, $day, $date, $relativeDate);
        $time = parent::escape($time);
        $weekday = parent::escape($weekday);
        $day = parent::escape($day);
        $date = parent::escape( date('Y-m-d H:i:s', strtotime($date)) );
        $how_many = parent::escape($how_many);
        
        $changed = FALSE;
        
        switch ($runs_every)
        {
            case 'd'://date
                if($time != $recurrentDet['time'])
                    $changed = true;
                break;
            case 'w'://week
                if($time != $recurrentDet['time'] || $weekday != $recurrentDet['weekday'])
                    $changed = true;
                break;
            case 'm'://month
                if($time != $recurrentDet['time'] || $day != $recurrentDet['day'])
                    $changed = true;
                break;
            case 'twoweek'://twoweek
                if($date != $recurrentDet['date'])
                    $changed = true;
                break;
			case 'y'://year
                if($date != $recurrentDet['date'])
                    $changed = true;
                break;
            
        }
        if(!$changed)
            return;
        
        $sql = "UPDATE check_recurrent SET runs_every = '{$runs_every}',  time = '{$time}',
            weekday = '{$weekday}', day = '{$day}', date = '{$date}', how_many = '{$how_many}', next_run = '{$next_run}'
            WHERE check_id = '{$check_id}'";
        $this->execute_query($sql);*/
        
    }
	/**
    * deleteRecurrence
    *
    * @desc - delete Recurrence
    *
    * @return 
    *
    */
    public function deleteRecurrence($check_id)
    {
        /*$sql = "DELETE FROM check_recurrent WHERE check_id = '" . (int) $check_id ."'";
        $this->execute_query($sql);*/
    }
	/**
    * trigger job
    *
    * @desc - trigger job
    *
    * @return 
    *
    */
    public function trigger_job()
    {
        /*$recurrent = $this->fetchRecurrentDetails(null, null, true);
        
        
        $this->setFetchLock($recurrent);
        
        foreach ($recurrent as $item)
        {
            $check = $this->admin->fetchCheckDetails($item['check_id']);
            if(!$check)
                return;
            $check_id = $this->processCheck($check);
            $this->registerAction($item);
            if($check_id)
                $this->admin->notify('notify_processed', array('id' => $check_id));
        }*/
    }
    
    /**
    * registerAction
    *
    * @desc - register Action 
    *
    * @return 
    *
    */
    public function registerAction($recurrent)
    {
        /*$nexr_run = $this->getNextRun($recurrent['runs_every'], $recurrent['time'],
                $recurrent['weekday'], $recurrent['day'], $recurrent['date'], $recurrent['relative_date']);
        $sql = "UPDATE check_recurrent SET last_run = NOW(), count_run = count_run + 1, next_run = '" . $nexr_run ."', fetch_lock = '0'
            WHERE id = '" . $recurrent['id'] . "'";
        $this->execute_query($sql);*/
    }
    /**
    * set Fetch Lock
    *
    * @desc - set Fetch Lock
    * @return 
    *
    */
    public function setFetchLock($array)
    {
        /*$ids = "";
        if(count($array)){
            foreach($array as $item)
            {
                $ids .= ($ids) ? "," : "";
                $ids .= "'" . $item['id'] . "'" ;
            }
            
            $sql = " UPDATE check_recurrent SET fetch_lock = '1' WHERE id IN (" . $ids . ")";
            $this->execute_query($sql);    
        }*/
        
       
    }
    
     
    /**
    * fetchToken
    *
    * @desc - fetch Token
    *  
    * @return array of items
    *
    */
    private function fetchToken()
    {
        /*$client = new SoapClient('https://gatewaydtx1.giact.com/gVerifyV2/POST/Verify.asmx?wsdl');
        try {
            $response = $client->Login(array("companyID" => "952", "un" => "fw7k_k-4du", "pw" => "XAgUd-_i7UTC"));
            $token = $response->{"LoginResult"};
            $_SESSION["TOKEN"] = $token;
            return $token;
        } catch (Exception $p) {

        }*/
    }
    
    /**
    * processCheck
    *
    * @desc - process Check
    *
    * @return array of items
    *
    */
    public  function processCheck($check)
    {
        /*$this->fetchToken();
        $client = new SoapClient('https://gatewaydtx1.giact.com/gVerifyV2/POST/Verify.asmx?wsdl');
        
        $check_id = 0;
        try
        {
            $token = $_SESSION['TOKEN'];
            $response = $client->Call(array("CompanyID" => 952, "Token" => $token,
                "RoutingNumber" => $check['routing'], "AccountNumber" => $check['Checking_AC_No'],
                "CheckNumber" => $check['chknum'], "Amount" => $check['ammount']));

            $token = $response->{"CallResult"};
            $token = explode("|", $token);
            $res = $token[3];
            
            $check['response_code'] = $res;
            
            $check_id = $this->insertCheck($check);
        } 
        catch (Exception $p)
        {
            error_log('Cron recurrence exeption: ' . $p->getMessage());
        }
        return $check_id;*/
    }
    
    /**
    * insertCheck
    *
    * @desc - insert Check
    *  
    * @return array of items
    *
    */
    private  function insertCheck($check)
    {

        /*$sql = "INSERT INTO check_add (name, address, city, state, zip, phone, memo, routing,"
            . " Checking_AC_No, confirm_account, ammount, chknum, ipaddress, date, authorisation_date, month, response_code,"
            . " to_name, user_id, company_id)"
            . " VALUES ('" . $this->escape($check['name']) . "', '" . $this->escape($check['address']) . "',"
            . " '" . $this->escape($check['city']) . "', '" . $this->escape($check['state']) . "',"
            . " '" . $this->escape($check['zip']) . "', '" . $this->escape($check['phone']) . "',"
            . " '" . $this->escape($check['memo']) . "', '" . $this->escape($check['routing']) . "',"
            . " '" . $this->escape($check['Checking_AC_No']) . "', '" . $this->escape($check['confirm_account']) . "',"
            . " '" . $this->escape($check['ammount']) . "', '" . $this->escape($check['chknum']) . "',"
            . " '" . $this->escape($check['ipaddress']) . "', NOW(), NOW(),'" . date("F") . "',"
            . " '" . $this->escape($check['response_code']) . "','" . $check['to_name'] . "',"
            . " '" . $check['user_id'] . "','" . $check['company_id'] . "')";
        $this->execute_query($sql);
        $check_id = $this->lastId();
        return $check_id;*/
    }
    
    /**
    * isCheckEnabledRecurrent
    *
    * @desc - is Check Enabled Recurrent
    *  
    * @return boolean
    *
    */
	public function isCheckEnabledRecurrent($check_id)
	{
		//$recurrent = $this->fetchRecurrentDetails(null, $check_id);
		//$ret =  ($recurrent) ? 1 : 0;
		//return $ret;
	}
}
