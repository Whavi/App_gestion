<?php

namespace App\Factory;

use App\Entity\Attribution;
use App\Repository\AttributionRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Attribution>
 *
 * @method        Attribution|Proxy create(array|callable $attributes = [])
 * @method static Attribution|Proxy createOne(array $attributes = [])
 * @method static Attribution|Proxy find(object|array|mixed $criteria)
 * @method static Attribution|Proxy findOrCreate(array $attributes)
 * @method static Attribution|Proxy first(string $sortedField = 'id')
 * @method static Attribution|Proxy last(string $sortedField = 'id')
 * @method static Attribution|Proxy random(array $attributes = [])
 * @method static Attribution|Proxy randomOrCreate(array $attributes = [])
 * @method static AttributionRepository|RepositoryProxy repository()
 * @method static Attribution[]|Proxy[] all()
 * @method static Attribution[]|Proxy[] createMany(int $number, array|callable $attributes = [])
 * @method static Attribution[]|Proxy[] createSequence(iterable|callable $sequence)
 * @method static Attribution[]|Proxy[] findBy(array $attributes)
 * @method static Attribution[]|Proxy[] randomRange(int $min, int $max, array $attributes = [])
 * @method static Attribution[]|Proxy[] randomSet(int $number, array $attributes = [])
 */
final class AttributionFactory extends ModelFactory
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
            'byUser' => UserFactory::new(),
            'dateAttribution' => self::faker()->dateTime(),
            'dateRestitution' => self::faker()->dateTime(),
            'collaborateur' => CollaborateurFactory::new(),
            'product' => ProductFactory::new(),
            'updatedAt' => self::faker()->dateTime(),
            'Rendu' => self::faker()->boolean('FALSE'),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            // ->afterInstantiate(function(Attribution $attribution): void {})
        ;
    }

    protected static function getClass(): string
    {
        return Attribution::class;
    }
}
