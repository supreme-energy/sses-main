<?php
// Get values 'm' and 'b' needed for line equation: y = mx + c

function GetLineEquation($x1,$x2,$y1,$y2,&$m,&$c)
{
	if($x2 == $x1) return false;
	$m = ($y2 - $y1)/($x2 - $x1);
	$c = $y1 - ($m * $x1);
	return true;
}

// Get the distance between two points

function GetPointsDist($x1,$x2,$y1,$y2)
{
	return sqrt(pow($x2 - $x1,2) + pow($y2 - $y1,2));
}

// Get shortest (perpendicular) distance from a point to a line assuming that
// equation to the line is: ax + by + c = 0

function GetDistPointLine($x,$y,$a,$b,$c)
{
	return abs(($a * $x) + ($b * $y) + $c) / sqrt(($a * $a) + ($b * $b));
}

// Find out if a point if left of a line created from the values a & b

function PointIsLeft($a,$b,$point)
{
    return (($b->x - $a->x)*($point->y - $a->y) - ($b->y - $a->y)*($point->x - $a->x)) > 0;
}
?>
