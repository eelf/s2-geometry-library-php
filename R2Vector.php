<?php

class R2Vector {
    private $x;
    private $y;

    public function __construct($x = null, $y = null) {
        if ($x !== null && $y !== null) {
            $this->x = $x;
            $this->y = $y;
        } else if ($x != null) {
            if (!is_array($x) || count($x) != 2) throw new \Exception("Points must have exactly 2 coordinates");
            $this->x = $x[0];
            $this->y = $x[1];
        } else {
            $this->x = 0;
            $this->y = 0;
        }
    }

    public function x() {
        return $this->x;
    }

    public function y() {
        return $this->y;
    }

    public function get($index) {
        if ($index > 1) {
            throw new \Exception($index);
        }
        return $index == 0 ? $this->x : $this->y;
    }
    /*
public static R2Vector add(final R2Vector p1, final R2Vector p2) {
return new R2Vector(p1.x + p2.x, p1.y + p2.y);
}

public static R2Vector mul(final R2Vector p, double m) {
return new R2Vector(m * p.x, m * p.y);
}

public double norm2() {
return (x * x) + (y * y);
}

public static double dotProd(final R2Vector p1, final R2Vector p2) {
return (p1.x * p2.x) + (p1.y * p2.y);
}

public double dotProd(R2Vector that) {
return dotProd(this, that);
}

public double crossProd(final R2Vector that) {
return this.x * that.y - this.y * that.x;
}

public boolean lessThan(R2Vector vb) {
if (x < vb.x) {
return true;
}
if (vb.x < x) {
return false;
}
if (y < vb.y) {
return true;
}
return false;
}

@Override
public boolean equals(Object that) {
if (!(that instanceof R2Vector)) {
return false;
}
R2Vector thatPoint = (R2Vector) that;
return this.x == thatPoint.x && this.y == thatPoint.y;
}

/**
* Calcualates hashcode based on stored coordinates. Since we want +0.0 and
* -0.0 to be treated the same, we ignore the sign of the coordinates.
*#/
@Override
public int hashCode() {
long value = 17;
value += 37 * value + Double.doubleToLongBits(Math.abs(x));
value += 37 * value + Double.doubleToLongBits(Math.abs(y));
return (int) (value ^ (value >>> 32));
}

@Override
public String toString() {
return "(" + x + ", " + y + ")";
}
*/
}
