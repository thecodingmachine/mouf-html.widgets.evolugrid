<?php

namespace Mouf\Html\Widgets\EvoluGrid\Utils;

use Twig\Loader\LoaderInterface;
use Twig\Source;

/**
 * This loader completely bypasses the loader mechanism, by directly passing the key as a template.
 * Useful in our very case.
 *
 * This is a reimplementation of Twig's String loader that has been deprecated.
 * We enable it back in our case because there won't be a million of different cache keys.
 * And yes, we know what we are doing :)
 */
class TwigStringLoader implements LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSource($name)
    {
        return $name;
    }
    /**
     * {@inheritdoc}
     */
    public function getCacheKey($name)
    {
        return $name;
    }
    /**
     * {@inheritdoc}
     */
    public function isFresh($name, $time)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceContext($name)
    {
        return new Source($name, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name)
    {
        return true;
    }
}
