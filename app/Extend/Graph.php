<?php

namespace App\Extend;

class Graph {
    public function calculate_wilks ($total, $bw, $gender)
	{
		if ($gender == 'm')
		{
			// male Coefficients
			$a = -216.0475144;
			$b = 16.2606339;
			$c = -0.002388645;
			$d = -0.00113732;
			$e = 7.01863E-06;
			$f = -1.291E-08;
		}
		else
		{
			// female Coefficients
			$a = 594.31747775582;
			$b = -27.23842536447;
			$c = 0.82112226871;
			$d = -0.00930733913;
			$e = 0.00004731582;
			$f = -0.00000009054;
		}
		$coeff = 500/($a + $b * $bw + pow($bw, 2) * $c + pow($bw, 3) * $d + pow($bw, 4) * $e + pow($bw, 5) * $f);
		return $coeff * $total;
	}
	public function calculate_sinclair ($total, $bw, $gender)
	{
		global $user;
		// valid until RIO 2016
		if ($gender == 'm')
		{
			// male Coefficients
			$a = 0.794358141;
			$b = 174.393;
		}
		else
		{
			// female Coefficients
			$a = 0.897260740;
			$b = 148.026;
		}
		$coeff = pow(10, ($a * pow(log10 ($bw / $b), 2)));
		return $coeff * $total;
	}
}
