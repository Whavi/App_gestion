<?php

namespace App\Factory;

use App\Entity\Collaborateur;
use App\Factory\DepartementFactory;
use App\Repository\CollaborateurRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Collaborateur>
 *
 * @method        Collaborateur|Proxy create(array|callable $attributes = [])
 * @method static Collaborateur|Proxy createOne(array $attributes = [])
 * @method static Collaborateur|Proxy find(object|array|mixed $criteria)
 * @method static Collaborateur|Proxy findOrCreate(array $attributes)
 * @method static Collaborateur|Proxy first(string $sortedField = 'id')
 * @method static Collaborateur|Proxy last(string $sortedField = 'id')
 * @method static Collaborateur|Proxy random(array $attributes = [])
 * @method static Collaborateur|Proxy randomOrCreate(array $attributes = [])
 * @method static CollaborateurRepository|RepositoryProxy repository()
 * @method static Collaborateur[]|Proxy[] all()
 * @method static Collaborateur[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Collaborateur[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static Collaborateur[]|Proxy[] findBy(array $attributes)
 * @method static Collaborateur[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static Collaborateur[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class CollaborateurFactory extends ModelFactory
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
            'departement' => DepartementFactory::new(),
            'nom' => self::faker()->lastName(),
            'prenom' => self::faker()->firstName(),
            'email' => self::faker()->email(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Collaborateur $collaborateur): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Collaborateur::class;
    }
}
