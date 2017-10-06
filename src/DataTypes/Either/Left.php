<?php declare(strict_types=1);

namespace Phantasy\DataTypes\Either;

use Phantasy\DataTypes\Maybe\{Maybe, Nothing};
use Phantasy\DataTypes\Validation\{Validation, Failure};
use Phantasy\Traits\CurryNonPublicMethods;
use function Phantasy\Core\{curry, identity};

final class Left extends Either
{
    use CurryNonPublicMethods;

    public $value = null;

    public function __construct($val)
    {
        $this->value = $val;
    }

    public function __toString()
    {
        return "Left(" . var_export($this->value, true) . ")";
    }

    private function equals(Either $e) : bool
    {
        return $this == $e;
    }

    private function concat(Either $e) : Either
    {
        return $e;
    }

    private function map(callable $f) : Either
    {
        return $this;
    }

    private function ap(Either $eitherWithFunction) : Either
    {
        return $this;
    }

    private function chain(callable $f) : Either
    {
        return $this;
    }

    private function extend(callable $f) : Either
    {
        return $this;
    }

    private function fold(callable $f, callable $g)
    {
        return $f($this->value);
    }

    private function bimap(callable $f, callable $g) : Either
    {
        return new static($f($this->value));
    }

    private function alt(Either $e) : Either
    {
        return $e;
    }

    private function reduce(callable $f, $acc)
    {
        return $acc;
    }

    private function traverse(string $className, callable $f)
    {
        if (!class_exists($className) || !is_callable([$className, 'of'])) {
            throw new \InvalidArgumentException(
                'Method must be a class name of an Applicative (must have an \'of\' method).'
            );
        }

        return call_user_func([$className, 'of'], new static($this->value));
    }

    private function sequence(string $className)
    {
        return $this->traverse($className, identity());
    }

    // Aliases
    private function bind(callable $f) : Either
    {
        return $this->chain($f);
    }

    private function flatMap(callable $f) : Either
    {
        return $this->chain($f);
    }

    private function cata($f, $g)
    {
        return $this->fold($f, $g);
    }

    // Conversions
    public function toMaybe() : Maybe
    {
        return new Nothing();
    }

    public function toValidation() : Validation
    {
        return new Failure($this->value);
    }
}

function Left(...$args)
{
    return curry(function ($x) {
        return new Left($x);
    })(...$args);
}
