<?php
class Holydate {
    
    public $Name, $Register;
    
    public function __construct() {
        $this->Register = new \stdClass;
    }
    
    /**
     * Kontrollerar om ett datum matchar en registrerad helg- eller högtidsdag (en eller flera) 
     * för ett angivet datum.
     * 
     * @param {string} $name Namn på helg- eller högtidsdag
     */
    public function Register($name) {
        $this->Name = $name;
        $reg = new \stdClass;
        if (!empty($name) && !isset($this->Register->$name)) {
            $reg->Type       = null;
            $reg->Weekday    = 1;
            $reg->Interval   = 1;
            $reg->MonthIndex = 1;
            $reg->DayIndex   = 1;
            $reg->Occurrence = 'first';
            $reg->Offset     = 0;
            $reg->IsRed      = false;
            $this->Register->$name = $reg;
        }
        return $this;
    }
    
    /**
     * Kontrollerar om ett datum matchar en registrerad helg- eller högtidsdag (en eller flera) 
     * för ett angivet datum.
     * 
     * @param {string} date Datumsträng
     * @returns {array|boolean} Array med namn på de dagar som matchar angivet datum eller false om ingen matchning finns
     */
    public function Check($input_date) {
        $input_ts = strtotime($input_date);
        $result = [];
        foreach($this->Register as $name => $item) {
            $day = new \stdClass;
            $day->Date    = $input_date;
            $day->DayName = $name;
            $day->IsRed   = $item->IsRed;
            if ($item->Type === 'easter' && $input_ts === $this->CalculateEaster($input_ts, $item)) {
                $result[] = $day;
            } else if ($item->Type === 'date' && $input_ts === $this->CalculateDate($input_ts, $item)) {
                $result[] = $day;
            } else if ($item->Type === 'weekday' && $input_ts === $this->CalculateWeekday($input_ts, $item)) {
                $result[] = $day;
            }
        }
        return !empty($result) ? $result : false;
    }
    
    /**
     * Sätter beräkningstypen till 'date'. Denna typ används för att beräkna
     * högtidsdagar som infaller på fasta datum. Tex. Första maj, Julafton, 
     * Nyårsafton osv.
     */
    public function Date() {
        $this->Register->{$this->Name}->Type = 'date';
        return $this;
    }
    
    /**
     * Sätter beräkningstypen till 'easter'. Denna typ används för att beräkna
     * högtidsdagar som infaller relativt till infallandet av påskdagen.
     * Tex. Dymmelonsdagen, Långfredagen, Kristi himmelfärd osv.
     */
    public function Easter() {
        $this->Register->{$this->Name}->Type = 'easter';
        return $this;
    }
    
    /**
     * Sätter beräkningstypen till 'weekday'. Denna typ används för att beräkna
     * högtidsdagar som infaller på fasta veckodagar, men på varierande datum.
     * Tex. Midsommarafton, Allhelgonaafton, Första advent osv.
     *  
     * @param {int} $weekday_index 1=mån...7=sön
     */
    public function Weekday($weekday_index) {
        $this->Register->{$this->Name}->Type = 'weekday';
        $this->Register->{$this->Name}->Weekday = intval($weekday_index);
        return $this;
    }
    
    /**
     * Sätter månad (månadsnummer).
     * 
     * @param {string} $month_index 1=jan...12=dec
     */
    public function Month($month_index) {
        $this->Register->{$this->Name}->MonthIndex = intval($month_index);
        return $this;
    }
    
    /**
     * Sätter dag (dagsnummer i månad).
     * 
     * @param string $day_index 1...31
     */
    public function Day($day_index) {
        $this->Register->{$this->Name}->DayIndex = intval($day_index);
        return $this;
    }
    
    /**
     * Sätter att den första veckodagen som matchar Weekday($weekday_index) 
     * ska retuneras.
     */
    public function First() {
        $this->Register->{$this->Name}->Occurrence = 'first';
        return $this;
    }
    
    /**
     * Sätter att den sista veckodagen som matchar Weekday($weekday_index) 
     * ska retuneras.
     */
    public function Last() {
        $this->Register->{$this->Name}->Occurrence = 'last';
        return $this;
    }
    
    /**
     * Sätter ett interval av antal veckor fram i tiden, för att bestämma i 
     * vilken vecka en viss helgdag (veckodag) ska inträffa.
     * 
     * @param int $num_weeks Antal veckor
     */
    public function Interval($num_weeks = 1) {
        $this->Register->{$this->Name}->Interval = intval($num_weeks);
        return $this;
    }
    
    /**
     * Sätter antal avvikande dagar (postivt eller negativt heltal) som ett 
     * datum ska avvika ifrån påskdagen.
     * 
     * @param int $num_days Antal dagar
     */
    public function Offset($offset_days = 0) {
        $this->Register->{$this->Name}->Offset = intval($offset_days);
        return $this;
    }
    
    /**
     * Sätter en högtidsdag till röd helgdag.
     */
    public function IsRed() {
        $this->Register->{$this->Name}->IsRed = true;
        return $this;
    }
    
    /**
     * Beräknar en tidsstämpel för infallande av påskdagen. Avvikande dagar 
     * från påskdagen tas med i beräkningen. Avvikande dagar sätts med 
     * funktionen offset().
     * 
     * @param int $input_ts Tidsstämpel för det datum som angivits
     * @param int $item Högtidsdag från $this->register, som ska kontrolleras
     * @return int Tidsstämpel för det datum som räknats fram
     */
    public function CalculateEaster($input_ts, $item) {
        $year = date('Y', $input_ts);
        $a = $year % 19;
        $b = floor($year / 100);
        $c = $year % 100;
        $d = floor($b / 4);
        $e = $b % 4;
        $f = floor(($b + 8) / 25);
        $g = floor(($b - $f + 1) / 3);
        $h = (((19*$a) + ($b - $d - $g)) + 15) % 30;
        $i = floor($c / 4);
        $k = $c % 4;
        $L = (32 + (2*$e) + (2*$i) - $h - $k) % 7;
        $m = floor(($a + (11*$h) + (22*$L)) / 451);
        $M = floor(($h + $L - (7*$m) + 114) / 31);
        $D = (($h + $L - (7*$m) + 114) % 31) + 1;
        $ts = strtotime($year.'-'.($M < 10 ? '0':'').$M.'-'.($D < 10 ? '0':'').$D);
        return $ts + ($item->Offset*86400);
    }
    
    /**
     * Beräkar en tidsstämpel för ett fast datum.
     * 
     * @param int $input_ts Tidsstämpel för det datum som angivits
     * @param int $item Högtidsdag från $this->register, som ska kontrolleras
     * @return int Tidsstämpel för det datum som räknats fram
     */
    public function CalculateDate($input_ts, $item) {
        $y = date('Y', $input_ts);
        $m = (intval($item->MonthIndex) < 9 ? '0' : '') . $item->MonthIndex;
        $d = (intval($item->DayIndex) < 9 ? '0' : '') . $item->DayIndex;
        return strtotime($y.'-'.$m.'-'.$d);
    }
    
    /**
     * Beräkar en tidsstämpel för när en viss veckodag infaller, ut ifrån givna 
     * avgränsningar. Avgränsningar sätts med funktionerna Weekday(), Month(), 
     * Day(), Interval(), First() och Last().
     * 
     * @param int $input_ts Tidsstämpel för det datum som angivits
     * @param int $item Högtidsdag från $this->register, som ska kontrolleras
     * @return int Tidsstämpel för det datum som räknats fram
     */
    public function CalculateWeekday($input_ts, $item) {
        $input_date = date('Y-m-d', $input_ts);
        $month      = (intval($item->MonthIndex) < 9 ? '0' : '') . $item->MonthIndex;
        $day        = (intval($item->DayIndex) < 9 ? '0' : '') . $item->DayIndex;
        $start_date = strtotime(date('Y', $input_ts) . $month . ($item->Occurrence === 'first' ? $day : '01'));
        
        if ($item->Occurrence === 'first') {
            $end_date = strtotime(date('Y-m-d', $start_date+(7*$item->Interval*86400)));
            $occurrences = 0;
            for ($i = $start_date; $i <= $end_date; $i += 86400) {
                $date = date('Y-m-d', $i);
                $weekday = intval(date('N', strtotime($date)));
                $occurrences += $weekday === $item->Weekday ? 1 : 0;
                if ($input_date === $date && $weekday === $item->Weekday && $occurrences === $item->Interval) {
                    return $i;
                }
            }
        } else if ($item->Occurrence === 'last') {
            $end_date = strtotime(date('Y-m-t', $start_date));
            $last_occurrence = 0;
            for ($i = $start_date; $i <= $end_date; $i += 86400) {
                $date = date('Y-m-d', $i);
                $weekday = intval(date('N', strtotime($date)));
                $last_occurrence = $weekday === $item->Weekday ? strtotime($date) : $last_occurrence;
                if ($input_date === $date && $weekday === $item->Weekday) {
                    return $last_occurrence;
                }
            }
        }
        return false;
    }
}
