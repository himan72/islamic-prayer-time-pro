<?php
/**------------------------------------------------------------------------
# Islamic Prayer Pro module by MH Oudrhiri, joomlar.net
# ------------------------------------------------------------------------
# author    M Hicham Oudrhiri http://www.joomlar.net/
# Copyright @2014 Joomlar.net.  All Rights Reserved.
# @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
# Website: http://www.joomlar.net/
-------------------------------------------------------------------------*/
// No direct access
defined( '_JEXEC' ) or die;
class modHijridateHelper
{
     /**
         * get the module params
         */
        static function getParams($instance = 'mod_islamic_prayer_time_pro'){
          jimport('joomla.application.module.helper');
          $module       = JModuleHelper::getModule($instance);
          $moduleParams = new JRegistry;
          $moduleParams->loadString($module->params);
          return $moduleParams;
        }
}

function ikamaTime($t, $m){
$time = strtotime($t);
return date("H:i", strtotime($m.' minutes', $time));
}


class PrayTime
{

    //------------------------ Constants --------------------------

    // Calculation Methods
    var $Jafari     = 0;    // Ithna Ashari
    var $Karachi    = 1;    // University of Islamic Sciences, Karachi
    var $ISNA       = 2;    // Islamic Society of North America (ISNA)
    var $MWL        = 3;    // Muslim World League (MWL)
    var $Makkah     = 4;    // Umm al-Qura, Makkah
    var $Egypt      = 5;    // Egyptian General Authority of Survey
    var $Custom     = 6;    // Custom Setting
    var $Tehran     = 7;    // Institute of Geophysics, University of Tehran
    var $UOIF       = 8;    // Union des Organisations Islamic de France


    // Juristic Methods
    var $Shafii     = 0;    // Shafii (standard)
    var $Hanafi     = 1;    // Hanafi

    // Adjusting Methods for Higher Latitudes
    var $None       = 0;    // No adjustment
    var $MidNight   = 1;    // middle of night
    var $OneSeventh = 2;    // 1/7th of night
    var $AngleBased = 3;    // angle/60th of night


    // Time Formats
    var $Time24     = 0;    // 24-hour format
    var $Time12     = 1;    // 12-hour format
    var $Time12NS   = 2;    // 12-hour format with no suffix
    var $Float      = 3;    // floating point number

    // Time Names
    var $timeNames = array(
        'Fajr',
        'Sunrise',
        'Dhuhr',
        'Asr',
        'Sunset',
        'Maghrib',
        'Isha'
    );

    var $InvalidTime = '-----';     // The string used for invalid times


    //---------------------- Global Variables --------------------


    var $calcMethod         = 0;        // caculation method
    var $asrJuristic        = 0;        // Juristic method for Asr
    var $fajrAdjMinutes     = 0;            // Fajr manual minutes adjusment
    var $dhuhrMinutes       = 0;        // minutes after mid-day for Dhuhr
    var $asrAdjMinutes      = 0;            // Asr manual minutes adjusment
    var $maghribAdjMinutes  = 0;            // Maghrib manual minutes adjusment
    var $ishaaAdjMinutes    = 0;            // Ishaa manual minutes adjusment
    var $chouroukAdjMinutes = 0;          // Chourouk manual minutes adjusment
    var $adjustHighLats     = 1;    // adjusting method for higher latitudes


    var $timeFormat   = 0;        // time format

    var $lat;        // latitude
    var $lng;        // longitude
    var $timeZone;   // time-zone
    var $JDate;      // Julian date


    //--------------------- Technical Settings --------------------


    var $numIterations = 1;        // number of iterations needed to compute times


    //------------------- Calc Method Parameters --------------------


    var $methodParams = array();

    /*  var $methodParams[methodNum] = array(fa, ms, mv, is, iv);

            fa : fajr angle
            ms : maghrib selector (0 = angle; 1 = minutes after sunset)
            mv : maghrib parameter value (in angle or minutes)
            is : isha selector (0 = angle; 1 = minutes after maghrib)
            iv : isha parameter value (in angle or minutes)
    */


    //----------------------- Constructors -------------------------


    function PrayTime($methodID = 0)
    {

        $this->methodParams[$this->Jafari]    = array(16, 0, 4, 0, 14);
        $this->methodParams[$this->Karachi]   = array(18, 1, 0, 0, 18);
        $this->methodParams[$this->ISNA]      = array(15, 1, 0, 0, 15);
        $this->methodParams[$this->MWL]       = array(18, 1, 0, 0, 17);
        $this->methodParams[$this->Makkah]    = array(18.5, 1, 0, 1, 90);
        $this->methodParams[$this->Egypt]     = array(19.5, 1, 0, 0, 17.5);
        $this->methodParams[$this->Tehran]    = array(17.7, 0, 4.5, 0, 14);
        $this->methodParams[$this->Custom]    = array(18, 1, 0, 0, 17);
        $this->methodParams[$this->UOIF]      = array(12, 1, 0, 0, 12);

        $this->setCalcMethod($methodID);
    }

    function __construct($methodID = 0)
    {
        $this->PrayTime($methodID);
    }



    //-------------------- Interface Functions --------------------


    // return prayer times for a given date
    function getDatePrayerTimes($year, $month, $day, $latitude, $longitude, $timeZone)
    {
        $this->lat = $latitude;
        $this->lng = $longitude;
        $this->timeZone = $timeZone;
        $this->JDate = $this->julianDate($year, $month, $day)- $longitude/ (15* 24);
        return $this->computeDayTimes();
    }

    // return prayer times for a given timestamp
    function getPrayerTimes($timestamp, $latitude, $longitude, $timeZone)
    {
        $date = @getdate($timestamp);
        return $this->getDatePrayerTimes($date['year'], $date['mon'], $date['mday'],
                    $latitude, $longitude, $timeZone);
    }

    // set the calculation method
    function setCalcMethod($methodID)
    {
        $this->calcMethod = $methodID;
    }

    // set the juristic method for Asr
    function setAsrMethod($methodID)
    {
        if ($methodID < 0 || $methodID > 1)
            return;
        $this->asrJuristic = $methodID;
    }

    // set the angle for calculating Fajr
    function setFajrAngle($angle)
    {
        $this->setCustomParams(array($angle, null, null, null, null));
    }

    // set the angle for calculating Maghrib
    function setMaghribAngle($angle)
    {
        $this->setCustomParams(array(null, 0, $angle, null, null));
    }

    // set the angle for calculating Isha
    function setIshaAngle($angle)
    {
        $this->setCustomParams(array(null, null, null, 0, $angle));
    }

    // set the minutes after mid-day for calculating Dhuhr
    function setDhuhrMinutes($minutes)
    {
        $this->dhuhrMinutes = $minutes;
    }
    // Manual add minutes for Asr
    function setAsrAdjt($minutes)
    {
        $this->asrAdjMinutes = $minutes;
    }
     // Manual add minutes for Maghrib
    function setMaghribAdjt($minutes)
    {
        $this->maghribAdjMinutes = $minutes;
    }
    // Manual add minutes for Ishaa
    function setIshaaAdjt($minutes)
        {
            $this->ishaaAdjMinutes = $minutes;
        }
        function setChouroukAdjt($minutes)
        {
            $this->chouroukAdjMinutes = $minutes;
        }

    // Manual add minutes for Ishaa
    function setFajrAdjt($minutes)
        {
            $this->fajrAdjMinutes = $minutes;
        }





    // set the minutes after Sunset for calculating Maghrib
    function setMaghribMinutes($minutes)
    {
        $this->setCustomParams(array(null, 1, $minutes, null, null));
    }

    // set the minutes after Maghrib for calculating Isha
    function setIshaMinutes($minutes)
    {
        $this->setCustomParams(array(null, null, null, 1, $minutes));
    }

    // set custom values for calculation parameters
    function setCustomParams($params)
    {
        for ($i=0; $i<5; $i++)
        {
            if ($params[$i] == null)
                $this->methodParams[$this->Custom][$i] = $this->methodParams[$this->calcMethod][$i];
            else
                $this->methodParams[$this->Custom][$i] = $params[$i];
        }
        $this->calcMethod = $this->Custom;
    }

    // set adjusting method for higher latitudes
    function setHighLatsMethod($methodID)
    {
        $this->adjustHighLats = $methodID;
    }

    // set the time format
    function setTimeFormat($timeFormat)
    {
        $this->timeFormat = $timeFormat;
    }

    // convert float hours to 24h format
    function floatToTime24($time)
    {
        if (is_nan($time))
            return $this->InvalidTime;
        $time = $this->fixhour($time+ 0.5/ 60);  // add 0.5 minutes to round
        $hours = floor($time);
        $minutes = floor(($time- $hours)* 60);
        return $this->twoDigitsFormat($hours). ':'. $this->twoDigitsFormat($minutes);
    }

    // convert float hours to 12h format
    function floatToTime12($time, $noSuffix = false)
    {
        if (is_nan($time))
            return $this->InvalidTime;
        $time = $this->fixhour($time+ 0.5/ 60);  // add 0.5 minutes to round
        $hours = floor($time);
        $minutes = floor(($time- $hours)* 60);
        $suffix = $hours >= 12 ? ' pm' : ' am';
        $hours = ($hours+ 12- 1)% 12+ 1;
        return $hours. ':'. $this->twoDigitsFormat($minutes). ($noSuffix ? '' : $suffix);
    }

    // convert float hours to 12h format with no suffix
    function floatToTime12NS($time)
    {
        return $this->floatToTime12($time, true);
    }



    //---------------------- Calculation Functions -----------------------

    // References:
    // http://www.ummah.net/astronomy/saltime


    // compute declination angle of sun and equation of time
    function sunPosition($jd)
    {
        $D = $jd - 2451545.0;
        $g = $this->fixangle(357.529 + 0.98560028* $D);
        $q = $this->fixangle(280.459 + 0.98564736* $D);
        $L = $this->fixangle($q + 1.915* $this->dsin($g) + 0.020* $this->dsin(2*$g));

        $R = 1.00014 - 0.01671* $this->dcos($g) - 0.00014* $this->dcos(2*$g);
        $e = 23.439 - 0.00000036* $D;

        $d = $this->darcsin($this->dsin($e)* $this->dsin($L));
        $RA = $this->darctan2($this->dcos($e)* $this->dsin($L), $this->dcos($L))/ 15;
        $RA = $this->fixhour($RA);
        $EqT = $q/15 - $RA;

        return array($d, $EqT);
    }

    // compute equation of time
    function equationOfTime($jd)
    {
        $sp = $this->sunPosition($jd);
        return $sp[1];
    }

    // compute declination angle of sun
    function sunDeclination($jd)
    {
        $sp = $this->sunPosition($jd);
        return $sp[0];
    }

    // compute mid-day (Dhuhr, Zawal) time
    function computeMidDay($t)
    {
        $T = $this->equationOfTime($this->JDate+ $t);
        $Z = $this->fixhour(12- $T);
        return $Z;
    }

    // compute time for a given angle G
    function computeTime($G, $t)
    {
        $D = $this->sunDeclination($this->JDate+ $t);
        $Z = $this->computeMidDay($t);
        $V = 1/15* $this->darccos((-$this->dsin($G)- $this->dsin($D)* $this->dsin($this->lat))/
                ($this->dcos($D)* $this->dcos($this->lat)));
        return $Z+ ($G>90 ? -$V : $V);
    }

    // compute the time of Asr
    function computeAsr($step, $t)  // Shafii: step=1, Hanafi: step=2
    {
        $D = $this->sunDeclination($this->JDate+ $t);
        $G = -$this->darccot($step+ $this->dtan(abs($this->lat- $D)));
        return $this->computeTime($G, $t);
    }


    //---------------------- Compute Prayer Times -----------------------


    // compute prayer times at given julian date
    function computeTimes($times)
    {
        $t = $this->dayPortion($times);

        $Fajr    = $this->computeTime(180- $this->methodParams[$this->calcMethod][0], $t[0]);
        $Sunrise = $this->computeTime(180- 0.833, $t[1]);
        $Dhuhr   = $this->computeMidDay($t[2]);
        $Asr     = $this->computeAsr(1+ $this->asrJuristic, $t[3]);
        $Sunset  = $this->computeTime(0.833, $t[4]);;
        $Maghrib = $this->computeTime($this->methodParams[$this->calcMethod][2], $t[5]);
        $Isha    = $this->computeTime($this->methodParams[$this->calcMethod][4], $t[6]);

        return array($Fajr, $Sunrise, $Dhuhr, $Asr, $Sunset, $Maghrib, $Isha);
    }


    // compute prayer times at given julian date
    function computeDayTimes()
    {
        $times = array(5, 6, 12, 13, 18, 18, 18); //default times

        for ($i=1; $i<=$this->numIterations; $i++)
            $times = $this->computeTimes($times);

        $times = $this->adjustTimes($times);
        return $this->adjustTimesFormat($times);
    }


    // adjust times in a prayer time array
    function adjustTimes($times)
    {
        for ($i=0; $i<7; $i++)
            $times[$i] += $this->timeZone- $this->lng/ 15;
        $times[0] += $this->fajrAdjMinutes/ 60; //Fajr
        $times[1] += $this->chouroukAdjMinutes/ 60; //Chourouk
        $times[2] += $this->dhuhrMinutes/ 60; //Dhuhr
        $times[3] += $this->asrAdjMinutes/ 60; //Asr
        $times[5] += $this->maghribAdjMinutes/ 60; //Maghrib
        $times[6] += $this->ishaaAdjMinutes/ 60; //Ishaa
        if ($this->methodParams[$this->calcMethod][1] == 1) // Maghrib
            $times[5] = $times[4]+ $this->methodParams[$this->calcMethod][2]/ 60;
        if ($this->methodParams[$this->calcMethod][3] == 1) // Isha
            $times[6] = $times[5]+ $this->methodParams[$this->calcMethod][4]/ 60;

        if ($this->adjustHighLats != $this->None)
            $times = $this->adjustHighLatTimes($times);
        return $times;
    }


    // convert times array to given time format
    function adjustTimesFormat($times)
    {
        if ($this->timeFormat == $this->Float)
            return $times;
        for ($i=0; $i<7; $i++)
            if ($this->timeFormat == $this->Time12)
                $times[$i] = $this->floatToTime12($times[$i]);
            else if ($this->timeFormat == $this->Time12NS)
                $times[$i] = $this->floatToTime12($times[$i], true);
            else
                $times[$i] = $this->floatToTime24($times[$i]);
        return $times;
    }


    // adjust Fajr, Isha and Maghrib for locations in higher latitudes
    function adjustHighLatTimes($times)
    {
        $nightTime = $this->timeDiff($times[4], $times[1]); // sunset to sunrise

        // Adjust Fajr
        $FajrDiff = $this->nightPortion($this->methodParams[$this->calcMethod][0])* $nightTime;
        if (is_nan($times[0]) || $this->timeDiff($times[0], $times[1]) > $FajrDiff)
            $times[0] = $times[1]- $FajrDiff;

        // Adjust Isha
        $IshaAngle = ($this->methodParams[$this->calcMethod][3] == 0) ? $this->methodParams[$this->calcMethod][4] : 18;
        $IshaDiff = $this->nightPortion($IshaAngle)* $nightTime;
        if (is_nan($times[6]) || $this->timeDiff($times[4], $times[6]) > $IshaDiff)
            $times[6] = $times[4]+ $IshaDiff;

        // Adjust Maghrib
        $MaghribAngle = ($this->methodParams[$this->calcMethod][1] == 0) ? $this->methodParams[$this->calcMethod][2] : 4;
        $MaghribDiff = $this->nightPortion($MaghribAngle)* $nightTime;
        if (is_nan($times[5]) || $this->timeDiff($times[4], $times[5]) > $MaghribDiff)
            $times[5] = $times[4]+ $MaghribDiff;

        return $times;
    }


    // the night portion used for adjusting times in higher latitudes
    function nightPortion($angle)
    {
        if ($this->adjustHighLats == $this->AngleBased)
            return 1/60* $angle;
        if ($this->adjustHighLats == $this->MidNight)
            return 1/2;
        if ($this->adjustHighLats == $this->OneSeventh)
            return 1/7;
    }


    // convert hours to day portions
    function dayPortion($times)
    {
        for ($i=0; $i<7; $i++)
            $times[$i] /= 24;
        return $times;
    }



    //---------------------- Misc Functions -----------------------


    // compute the difference between two times
    function timeDiff($time1, $time2)
    {
        return $this->fixhour($time2- $time1);
    }


    // add a leading 0 if necessary
    function twoDigitsFormat($num)
    {
        return ($num <10) ? '0'. $num : $num;
    }



    //---------------------- Julian Date Functions -----------------------


    // calculate julian date from a calendar date
    function julianDate($year, $month, $day)
    {
        if ($month <= 2)
        {
            $year -= 1;
            $month += 12;
        }
        $A = floor($year/ 100);
        $B = 2- $A+ floor($A/ 4);

        $JD = floor(365.25* ($year+ 4716))+ floor(30.6001* ($month+ 1))+ $day+ $B- 1524.5;
        return $JD;
    }


    // convert a calendar date to julian date (second method)
    function calcJD($year, $month, $day)
    {
        $J1970 = 2440588.0;
        $date = $year. '-'. $month. '-'. $day;
        $ms = strtotime($date);   // # of milliseconds since midnight Jan 1, 1970
        $days = floor($ms/ (1000 * 60 * 60* 24));
        return $J1970+ $days- 0.5;
    }


    //---------------------- Trigonometric Functions -----------------------

    // degree sin
    function dsin($d)
    {
        return sin($this->dtr($d));
    }

    // degree cos
    function dcos($d)
    {
        return cos($this->dtr($d));
    }

    // degree tan
    function dtan($d)
    {
        return tan($this->dtr($d));
    }

    // degree arcsin
    function darcsin($x)
    {
        return $this->rtd(asin($x));
    }

    // degree arccos
    function darccos($x)
    {
        return $this->rtd(acos($x));
    }

    // degree arctan
    function darctan($x)
    {
        return $this->rtd(atan($x));
    }

    // degree arctan2
    function darctan2($y, $x)
    {
        return $this->rtd(atan2($y, $x));
    }

    // degree arccot
    function darccot($x)
    {
        return $this->rtd(atan(1/$x));
    }

    // degree to radian
    function dtr($d)
    {
        return ($d * M_PI) / 180.0;
    }

    // radian to degree
    function rtd($r)
    {
        return ($r * 180.0) / M_PI;
    }

    // range reduce angle in degrees.
    function fixangle($a)
    {
        $a = $a - 360.0 * floor($a / 360.0);
        $a = $a < 0 ? $a + 360.0 : $a;
        return $a;
    }

    // range reduce hours to 0..23
    function fixhour($a)
    {
        $a = $a - 24.0 * floor($a / 24.0);
        $a = $a < 0 ? $a + 24.0 : $a;
        return $a;
    }

}


/**
 * A Hifri date class for Joomla 2.5 & 3.x modules
 */
class hijriDates{
    public $cdate = array() ;
    public $offset = 0;

    /**
     * Loads initialize values
     * @param array  $cdate  this a getdate()
     * @param integer $offset The Hijri date correction +/- 1,2
     */
    public function __construct($cdate, $offset=0){

        $this->cdate  = $cdate;
        $this->offset = $offset;
    }

    public function intPart($float)
    {
        if ($float < -0.0000001)
            return ceil($float - 0.0000001);
        else
            return floor($float + 0.0000001);
    }

    /**
     * Greg2Hijr function that calculate the Hijri date
     * @param integer  $day
     * @param integer  $month
     * @param integer  $year
     * @param boolean $string
     * @author Hicham Oudrhiri <info@joomlar.net>
     */
    public function Greg2Hijri()
    {
            $day   = $this->cdate['mday'];
            $month = $this->cdate['mon'];
            $year  = (int) $this->cdate['year'];

            if (($year > 1582) or (($year == 1582) and ($month > 10)) or (($year == 1582) and ($month == 10) and ($day > 14)))
            {
            $jd = $this->intPart((1461*($year+4800+$this->intPart(($month-14)/12)))/4)+$this->intPart((367*($month-2-12*($this->intPart(($month-14)/12))))/12)-
            $this->intPart( (3* ($this->intPart(  ($year+4900+    $this->intPart( ($month-14)/12)     )/100)    )   ) /4)+$day-32075;
            }
            else
            {
            $jd = 367*$year-$this->intPart((7*($year+5001+$this->intPart(($month-9)/7)))/4)+$this->intPart((275*$month)/9)+$day+1729777;
            }


            $l = $jd-1948440+10632;
            $n = $this->intPart(($l-1)/10631);
            $l = $l-10631*$n+354;
            $j = ($this->intPart((10985-$l)/5316))*($this->intPart((50*$l)/17719))+($this->intPart($l/5670))*($this->intPart((43*$l)/15238));
            $l = $l-($this->intPart((30-$j)/15))*($this->intPart((17719*$j)/50))-($this->intPart($j/16))*($this->intPart((15238*$j)/43))+29;

            $month = $this->intPart((24*$l)/709);
            $day   = $l-$this->intPart((709*$month)/24);
            $year  = 30*$n+$j-30;

            $date          = array();
            $date['year']  = $year;
            $date['month'] = $month;
            $date['day']   = $day;


            return $date;

    }


    /**
     * getMonthName function that set the Hijri month name in Arabic
     * @param  integer    $m the Numeric representation of a month
     * @return string     The arbic Hijri monnth name
     * @author Hicham Oudrhiri <info@joomlar.net>
     */
    public function getArMonthName($m) {


                    switch ($m) {
                        case 1 :
                            return 'المحرّم';
                        case 2 :
                            return 'صفر';
                        case 3 :
                            return 'ربيع الأوّل';
                        case 4 :
                            return 'ربيع الثاني';
                        case 5 :
                            return 'جمادى الأولى';
                        case 6 :
                            return 'جمادى الثانية';
                        case 7 :
                            return 'رجب';
                        case 8 :
                            return 'شعبان';
                        case 9 :
                            return 'رمضان';
                        case 10 :
                            return 'شوّال';
                        case 11 :
                            return 'ذو القعدة';
                        case 12 :
                            return 'ذو الحجّة';
                        default :
                            return 'رقم شهري خاطئ';
                    }
    }

    /**
     * getting custom Hiji Latin names  from the module params
     * @return array Custum Hijri month in array
     */
    public function getData() {
                $params = modHijridateHelper::getParams('mod_islamic_prayer_time_pro');
                $cmonth =array();
                $cm1    = $params->get('m1', 'Muharram');
                $cm2    = $params->get('m2','Safar');
                $cm3    = $params->get('m3','Rabbi al-Awwal');
                $cm4    = $params->get('m4','Rabbi al-Thanni');
                $cm5    = $params->get('m5','Jumada al-Ula');
                $cm6    = $params->get('m6','Jumada al-Thanni');
                $cm7    = $params->get('m7','Rajab');
                $cm8    = $params->get('m8','Shaaban');
                $cm9    = $params->get('m9','Ramadan');
                $cm10   = $params->get('m10','Shawwal');
                $cm11   = $params->get('m11','Dhul-Qadah');
                $cm12   = $params->get('m12','Dhul-Hijjah');
                $cmonth = array($cm1,$cm2,$cm3,$cm4,$cm5,$cm6,$cm7,$cm8,$cm9,$cm10,$cm11,$cm12);
                return $cmonth;
    }

    /**
     * getMonthName function that set the Hijri month name in Latin
     * @param  integer    $m the Numeric representation of a month
     * @return string     The Latin Hijri monnth name
     * @author Hicham Oudrhiri <info@joomlar.net>
     */
    public function getLtMonthName($m) {
        $cmonth =$this->getData();

                    switch ($m) {
                        case 1 :
                            return $cmonth[0];
                        case 2 :
                            return $cmonth[1];
                        case 3 :
                            return $cmonth[3];
                        case 4 :
                            return $cmonth[3];
                        case 5 :
                            return $cmonth[4];
                        case 6 :
                            return $cmonth[5];
                        case 7 :
                            return $cmonth[6];
                        case 8 :
                            return $cmonth[7];
                        case 9 :
                            return $cmonth[8];
                        case 10 :
                            return $cmonth[9];
                        case 11 :
                            return $cmonth[10];
                        case 12 :
                            return $cmonth[11];
                        default :
                            return 'Invalid Month Number';
                    }
    }

    /**
     * Get the hijri date day name in the arabic language
     * @param  integer $d    The Numeric representation of the day of the week
     * @return string        The day name in Arabic
     * @author Hicham Oudrhiri <info@joomlar.net>
     */
    public function getDayName() {

            $d = $this->cdate['wday'];

                    switch ($d) {
                        case 0 :
                            return 'الأحد';
                        case 1 :
                            return 'الإثنين';
                        case 2 :
                            return 'الثلاثا';
                        case 3 :
                            return 'الأربعاء';
                        case 4 :
                            return 'الخميس';
                        case 5 :
                            return 'الجمعة';
                        case 6 :
                            return 'السبت';
                        default :
                            return 'رقم شهري خاطئ';
                    }

    }

    /**
     * hijriArabic return the Hijri date in Arabic language
     * @return  array          the hijri date translated in arabic in an array
     * @author Hicham Oudrhiri <info@Joomlar.net>
     */
    public function hijriArabic (){

        //Getting hijri Date
        $hijriDate      = $this->Greg2Hijri();
        $ryear          = $hijriDate["year"];
        $m              = $hijriDate["month"];

        // Getting Hijri month name in Arabic
        $rmonth         =$this->getArMonthName($m);

        // Setting correction of Hijri date
        $rday           = $hijriDate["day"]+$this->offset;

        // Getting the Hijri day name in Arabic
        $arday          = $this->getDayName($this->cdate['wday']);

        // return value
        $arabichijidate =array($ryear,$rmonth,$rday,$arday);
        return $arabichijidate;

    }

    /**
     * hijriLatin function that return the Hijri date in latin
     * @return  array          the hijri date translated in Latin in an array
     * @author Hicham Oudrhiri <info@Joomlar.net>
     */
    public function hijriLatin (){

        //Getting hijri Date
        $hijriDate      = $this->Greg2Hijri();
        $ryear          = $hijriDate["year"];
        $m              = $hijriDate["month"];

        // Getting Hijri month name in Arabic
        $rmonth         =$this->getLtMonthName($m);

        // Setting correction of Hijri date
        $rday           = $hijriDate["day"]+$this->offset;

        // Getting the Hijri day name in Latin
        $latday         = $this->cdate['weekday'];

        // return value in an array
        $arabichijidate =array($ryear,$rmonth,$rday,$latday);
        return $arabichijidate;

    }
}