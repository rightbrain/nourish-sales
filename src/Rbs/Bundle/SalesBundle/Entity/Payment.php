<?php
namespace Rbs\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;

/**
 * Payment
 *
 * @ORM\Table(name="payments")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\SalesBundle\Repository\PaymentRepository")
 * @ORMSubscribedEvents()
 */
class Payment
{
    const PAYMENT_METHOD_CACHE = 'CACHE';
    const PAYMENT_METHOD_CHEQUE = 'CHEQUE';
    const PAYMENT_METHOD_BANK = 'BANK';

    use ORMBehaviors\Timestampable\Timestampable,
        ORMBehaviors\SoftDeletable\SoftDeletable,
        ORMBehaviors\Blameable\Blameable;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="Order", inversedBy="payments")
     * @ORM\JoinTable(name="join_payments_orders_",
     *      joinColumns={@ORM\JoinColumn(name="order_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="payment_id", referencedColumnName="id")}
     * )
     */
    protected $orders;

    /**
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\SalesBundle\Entity\Customer", inversedBy="payments", cascade={"persist"})
     * @ORM\JoinColumn(name="customer", nullable=false)
     */
    private $customer;

    /**
     * @var array $type
     *
     * @ORM\Column(name="payment_method", type="string", length=255, columnDefinition="ENUM('CACHE', 'CHEQUE', 'BANK')", nullable=false)
     */
    private $paymentMethod = 'BANK';

    /**
     * @var string
     *
     * @ORM\Column(name="bank_name", type="string", length=250, nullable=true)
     */
    private $bankName;

    /**
     * @var string
     *
     * @ORM\Column(name="branch_name", type="string", length=250, nullable=true)
     */
    private $branchName;

    /**
     * @var float
     *
     * @ORM\Column(name="amounts", type="float", nullable=false)
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="remarks", type="text", nullable=true)
     */
    private $remark;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return (string)$this->getId();
    }

    /**
     * @return string
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * @param string $remark
     */
    public function setRemark($remark)
    {
        $this->remark = $remark;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return array
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @param array $paymentMethod
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * @param string $bankName
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;
    }

    /**
     * @return string
     */
    public function getBranchName()
    {
        return $this->branchName;
    }

    /**
     * @param string $branchName
     */
    public function setBranchName($branchName)
    {
        $this->branchName = $branchName;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }
}