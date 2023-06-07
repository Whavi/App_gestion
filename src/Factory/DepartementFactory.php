<?php

namespace App\Factory;

use App\Entity\Departement;
use App\Repository\DepartementRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Departement>
 *
 * @method        Departement|Proxy create(array|callable $attributes = [])
 * @method static Departement|Proxy createOne(array $attributes = [])
 * @method static Departement|Proxy find(object|array|mixed $criteria)
 * @method static Departement|Proxy findOrCreate(array $attributes)
 * @method static Departement|Proxy first(string $sortedField = 'id')
 * @method static Departement|Proxy last(string $sortedField = 'id')
 * @method static Departement|Proxy random(array $attributes = [])
 * @method static Departement|Proxy randomOrCreate(array $attributes = [])
 * @method static DepartementRepository|RepositoryProxy repository()
 * @method static Departement[]|Proxy[] all()
 * @method static Departement[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Departement[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static Departement[]|Proxy[] findBy(array $attributes)
 * @method static Departement[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static Departement[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class DepartementFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function getDefaults(): array
    {
        return [
            'createAt' => self::faker()->dateTime(),
            'nom' => self::faker()->text(10),
            'updateAt' => self::faker()->dateTime(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Departement $departement): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Departement::class;
    }
}
