<?php

namespace Phantasy\Test\Traits;

use function Phantasy\Core\{curry, compose, id, concat};
use Phantasy\DataTypes\Maybe\Maybe;
use Phantasy\DataTypes\Validation\Validation;
use Eris\Generator;

trait LawAssertions
{
    use \Eris\TestTrait;

    public function assertSetoidLaws(callable $of)
    {
        $this->forAll(Generator\int(), Generator\int(), Generator\int())
            ->then(function ($a, $b, $c) use ($of) {
                // reflexivity
                $this->assertTrue($of($a)->equals($of($a)));

                // symmetry
                $this->assertEquals($of($a)->equals($of($b)), $of($b)->equals($of($a)));

                // transitivity
                if ($of($a)->equals($of($b)) && $of($b)->equals($of($c))) {
                    $this->assertTrue($of($a)->equals($of($c)));
                }
            });
    }

    public function assertOrdLaws(callable $of)
    {
        $this->forAll(Generator\int(), Generator\int(), Generator\int())
            ->then(function ($a, $b, $c) use ($of) {
                // totality
                $this->assertTrue($of($a)->lte($of($b)) || $of($b)->lte($of($a)));

                // antisymmetry
                if ($of($a)->lte($of($b)) && $of($b)->lte($of($a))) {
                    $this->assertTrue($of($a)->equals($of($b)));
                }

                // transitivity
                if ($of($a)->lte($of($b)) && $of($b)->lte($of($c))) {
                    $this->assertTrue($of($a)->lte($of($c)));
                }
            });
    }

    public function assertSemigroupoidLaws(callable $of)
    {
        $this->forAll(Generator\int(), Generator\int(), Generator\int())
            ->then(function ($a, $b, $c) use ($of) {
                // associativity
                $this->assertEquals(
                    $of($a)->compose($of($b))->compose($of($c)),
                    $of($a)->compose($of($b)->compose($of($c)))
                );
            });
    }

    public function assertCategoryLaws(string $clss, callable $of)
    {
        $this->forAll(Generator\int())
            ->then(function ($a) use ($clss, $of) {
                // right identity
                $this->assertEquals(
                    $of($a)->compose($clss::id()),
                    $of($a)
                );

                // left identity
                $this->assertEquals(
                    $clss::id()->compose($of($a)),
                    $of($a)
                );
            });
    }

    public function assertSemigroupLaws(callable $of)
    {
        $this->forAll(Generator\int(), Generator\int(), Generator\int())
            ->then(function ($a, $b, $c) use ($of) {
                // associativity
                $this->assertEquals(
                    $of($a)->concat($of($b))->concat($of($c)),
                    $of($a)->concat($of($b)->concat($of($c)))
                );
            });
    }

    public function assertMonoidLaws(string $clss, callable $of)
    {
        $this->forAll(Generator\int())
            ->then(function ($a) use ($clss, $of) {
                // right identity
                $this->assertEquals(
                    $of($a)->concat($clss::empty()),
                    $of($a)
                );

                // left identity
                $this->assertEquals(
                    $clss::empty()->concat($of($a)),
                    $of($a)
                );
            });
    }

    public function assertGroupLaws(string $clss, callable $of)
    {
        $this->forAll(Generator\int())
            ->then(function ($a) use ($clss, $of) {
                // right inverse
                $this->assertEquals(
                    $of($a)->concat($of($a)->invert()),
                    $clss::empty()
                );

                // left inverse
                $this->assertEquals(
                    $of($a)->invert()->concat($of($a)),
                    $clss::empty()
                );
            });
    }

    public function assertFunctorLaws(callable $of)
    {
        $this->forAll(Generator\string())
            ->then(function ($a) use ($of) {
                $f = concat('foo');
                $g = concat('bar');

                // identity
                $this->assertEquals($of($a)->map(id()), $of($a));

                // composition
                $this->assertEquals(
                    $of($a)->map($g)->map($f),
                    $of($a)->map(compose($f, $g))
                );
            });
    }

    public function assertContravariantLaws(callable $of)
    {
        $this->forAll(Generator\string())
            ->then(function ($a) use ($of) {
                $f = concat('foo');
                $g = concat('bar');

                // identity
                $this->assertEquals($of($a)->contramap(id()), $of($a));

                // composition
                $this->assertEquals(
                    $of($a)->contramap(compose($f, $g)),
                    $of($a)->contramap($f)->contramap($g)
                );
            });
    }

    public function assertApplyLaws(callable $of)
    {
        $this->forAll(Generator\int())
            ->then(function ($a) use ($of) {
                $f = function ($x) {
                    return $x + 4;
                };

                $g = function ($y) {
                    return $y / 2;
                };

                $compose = curry(function ($f, $g, $x) {
                    return $f($g($x));
                });

                // composition
                $this->assertEquals(
                    $of($a)->ap($of($g)->ap($of($f)->map($compose))),
                    $of($a)->ap($of($g))->ap($of($f))
                );
            });
    }

    public function assertApplicativeLaws(string $clss, callable $of)
    {
        $this->forAll(Generator\int())
            ->then(function ($a) use ($of, $clss) {
                $f = function ($x) {
                    return $x + 4;
                };

                // identity
                $this->assertEquals(
                    $of($a)->ap($clss::of(id())),
                    $of($a)
                );

                // homomorphism
                $this->assertEquals(
                    $clss::of($a)->ap($clss::of($f)),
                    $clss::of($f($a))
                );

                // interchange
                $this->assertEquals(
                    $clss::of($a)->ap($of($f)),
                    $of($f)->ap($clss::of(function ($f) use ($a) {
                        return $f($a);
                    }))
                );
            });
    }

    public function assertAltLaws(callable $of)
    {
        $this->forAll(Generator\int(), Generator\int(), Generator\int())
            ->then(function ($a, $b, $c) use ($of) {
                $f = function ($x) {
                    return $x / 2;
                };

                // associativity
                $this->assertEquals(
                    $of($a)->alt($of($b))->alt($of($c)),
                    $of($a)->alt($of($b)->alt($of($c)))
                );

                // distributivity
                $this->assertEquals(
                    $of($a)->alt($of($b))->map($f),
                    $of($a)->map($f)->alt($of($b)->map($f))
                );
            });
    }

    public function assertPlusLaws(string $clssName, callable $of)
    {
        $this->forAll(Generator\int())
            ->then(function ($a) use ($clssName, $of) {
                $f = function ($x) {
                    return $x + 1;
                };

                // right identity
                $this->assertEquals($of($a)->alt($clssName::zero()), $of($a));

                // left identity
                $this->assertEquals($clssName::zero()->alt($of($a)), $of($a));

                // annihilation
                $this->assertEquals($clssName::zero()->map($f), $clssName::zero());
            });
    }

    public function assertAlternativeLaws(string $clssName, callable $of)
    {
        $this->forAll(Generator\int())
            ->then(function ($a) use ($clssName, $of) {
                $f = function ($x) {
                    return $x - 2;
                };

                $g = function ($x) {
                    return $x % 4;
                };

                // distributivity
                $this->assertEquals(
                    $of($a)->ap($of($f)->alt($of($g))),
                    $of($a)->ap($of($f))->alt($of($a)->ap($of($g)))
                );

                // annihilation
                $this->assertEquals(
                    $of($a)->ap($clssName::zero()),
                    $clssName::zero()
                );
            });
    }

    public function assertTraversableLaws(string $clssName, callable $of)
    {
        $this->forAll(Generator\int())
            ->then(function ($a) use ($of) {
                $t = function (Maybe $m) : Validation {
                    return $m->toValidation(null);
                };

                // naturality
                $this->assertEquals(
                    $t($of(Maybe::of($a))->traverse(Maybe::class, id())),
                    $of(Maybe::of($a))->traverse(Validation::class, $t)
                );

                // identity
                $this->assertEquals(
                    $of($a)->traverse(Maybe::class, Maybe::of()),
                    Maybe::of($of($a))
                );

                $Compose = function ($c) {
                    return new class ($c) {
                        public $c = null;

                        public function __construct($c)
                        {
                            $this->c = $c;
                        }

                        public function ap($fc)
                        {
                            return new static($this->c->ap($f->c->map(curry(function ($a, $b) {
                                return $b->ap($a);
                            }))));
                        }

                        public function map($f)
                        {
                            return new static($this->c->map(function ($y) {
                                return $y->map($f);
                            }));
                        }
                    };
                };

                // composition
            });
    }

    public function assertChainLaws(callable $of)
    {
        $this->forAll(Generator\int())
            ->then(function ($a) use ($of) {
                $f = function ($x) use ($of) {
                    return $of($x % 2);
                };
                $g = function ($x) use ($of) {
                    return $of($x / 3);
                };

                // associativity
                $this->assertEquals(
                    $of($a)->chain($f)->chain($g),
                    $of($a)->chain(function ($x) {
                        return $f($x)->chain($g);
                    })
                );
            });
    }

    public function assertChainRecLaws($a)
    {
    }

    public function assertMonadLaws(string $clssName, callable $of)
    {
        $this->forAll(Generator\int())
            ->then(function ($a) use ($clssName, $of) {
                $f = function ($x) use ($of) {
                    return $of($x + 1);
                };

                // left identity
                $this->assertEquals($clssName::of($a)->chain($f), $f($a));

                // right identity
                $this->assertEquals($of($a)->chain($clssName::of()), $of($a));
            });
    }

    public function assertExtendLaws(callable $of)
    {
        $this->forAll(Generator\int())
            ->then(function ($a) use ($of) {
                $f = function ($x) use ($a) {
                    return $a + 5;
                };

                $g = function ($x) use ($a) {
                    return $a % 3;
                };

                $this->assertEquals(
                    $of($a)->extend($g)->extend($f),
                    $of($a)->extend(function ($w_) use ($f, $g) {
                        return $f($w_->extend($g));
                    })
                );
            });
    }

    public function assertComonadLaws(callable $of)
    {
        $this->forAll(Generator\int())
            ->then(function ($a) use ($of) {
                // left identity
                $this->assertEquals(
                    $of($a)->extend(function ($_w) {
                        return $_w->extract();
                    }),
                    $of($a)
                );

                $f = function ($w) use ($a) {
                    return $a + 5;
                };

                // right identity
                $this->assertEquals(
                    $of($a)->extend($f)->extract(),
                    $f($of($a))
                );
            });
    }

    public function assertBifunctorLaws(callable $of)
    {
        $this->forAll(Generator\int())
            ->then(function ($a) use ($of) {
                // identity
                $this->assertEquals($of($a)->bimap(id(), id()), $of($a));

                $f = function ($x) {
                    return $x + 2;
                };

                $g = function ($x) {
                    return $x - 2;
                };

                $h = function ($x) {
                    return $x / 2;
                };

                $i = function ($x) {
                    return $x % 2;
                };

                // composition
                $this->assertEquals(
                    $of($a)->bimap(compose($f, $g), compose($h, $i)),
                    $of($a)->bimap($g, $i)->bimap($f, $h)
                );
            });
    }

    public function assertProfunctorLaws(callable $of)
    {
        $this->forAll(Generator\int())
            ->then(function ($a) use ($of) {
                // identity
                $this->assertEquals($of($a)->promap(id(), id()), $of($a));

                $f = function ($x) {
                    return $x + 2;
                };

                $g = function ($x) {
                    return $x - 2;
                };

                $h = function ($x) {
                    return $x / 2;
                };

                $i = function ($x) {
                    return $x % 2;
                };

                // composition
                $this->assertEquals(
                    $of($a)->promap(compose($f, $g), compose($h, $i)),
                    $of($a)->promap($f, $i)->bimap($g, $h)
                );
            });
    }
}
