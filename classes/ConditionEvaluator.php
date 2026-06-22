<?php

class ConditionEvaluator
{
    public function evaluate(array $checkin): array
    {
        $conditions = [];

        // SLEEP
        if (isset($checkin['sleep_hours'])) {
            $sleep = (float) $checkin['sleep_hours'];
            if ($sleep < 5)        $conditions['sleep'] = 'severe';
            elseif ($sleep < 6.5)  $conditions['sleep'] = 'moderate';
            elseif ($sleep < 7.5)  $conditions['sleep'] = 'mild';
        }

        // HEALTH (general wellbeing, derived from mood_score: 1=low, 5=great)
        if (isset($checkin['mood_score'])) {
            $mood = (int) $checkin['mood_score'];
            if ($mood <= 2)        $conditions['health'] = 'severe';
            elseif ($mood === 3)   $conditions['health'] = 'moderate';
            elseif ($mood === 4)   $conditions['health'] = 'mild';
        }

        // ACADEMIC — gpa_self_report assumed on PUP's 1.00 (best) to 5.00 (fail)
        // scale. Confirm the exact thresholds with your actual grading policy
        // before defending this — the numbers below are a reasonable starting
        // point, not an official cutoff.
        if (isset($checkin['attendance_pct']) || isset($checkin['gpa_self_report'])) {
            $attendance = (float) ($checkin['attendance_pct'] ?? 100);
            $gpa        = (float) ($checkin['gpa_self_report'] ?? 1.0);

            if ($attendance < 75 || $gpa >= 3.0)   $conditions['academic'] = 'severe';
            elseif ($gpa >= 2.5)                    $conditions['academic'] = 'moderate';
            elseif ($gpa >= 2.0)                    $conditions['academic'] = 'mild';
        }

        // SCHEDULE
        if (isset($checkin['workload_score'])) {
            $workload = (int) $checkin['workload_score'];
            if ($workload >= 5)        $conditions['schedule'] = 'severe';
            elseif ($workload === 4)   $conditions['schedule'] = 'moderate';
            elseif ($workload === 3)   $conditions['schedule'] = 'mild';
        }

        // SOCIAL
        if (isset($checkin['social_score'])) {
            $social = (int) $checkin['social_score'];
            if ($social <= 1)        $conditions['social'] = 'severe';
            elseif ($social === 2)   $conditions['social'] = 'moderate';
            elseif ($social === 3)   $conditions['social'] = 'mild';
        }

        // FINANCIAL
        if (isset($checkin['financial_stress_score'])) {
            $financial = (int) $checkin['financial_stress_score'];
            if ($financial >= 5)        $conditions['financial'] = 'severe';
            elseif ($financial === 4)   $conditions['financial'] = 'moderate';
            elseif ($financial === 3)   $conditions['financial'] = 'mild';
        }

        return $conditions; // e.g. ['sleep' => 'moderate', 'academic' => 'severe']
    }
}