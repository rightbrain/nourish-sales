<?php

namespace Rbs\Bundle\SalesBundle\Form\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class OrderIdDataTransformer implements DataTransformerInterface
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Transforms an object (issue) to a string (number).
     *
     * @param  Order|null $order
     * @return string
     */
    public function transform($order)
    {
        if (null === $order) {
            return '';
        }

        return $order->getId();
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string $orderId
     * @return Order|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($orderId)
    {
        // no issue number? It's optional, so that's ok
        if (!$orderId) {
            return;
        }

        $order = $this->manager
            ->getRepository('RbsSalesBundle:Order')
            // query for the issue with this id
            ->find($orderId)
        ;

        if (null === $order) {
            // causes a validation error
            // this message is not shown to the user
            // see the invalid_message option
            throw new TransformationFailedException(sprintf(
                'An order with number "%s" does not exist!',
                $orderId
            ));
        }

        return $order;
    }
}