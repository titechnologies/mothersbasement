<?php
class Meanbee_Royalmail_Test_Model_Shipping_Carrier_Royalmail_Specialdelivery9am50 extends Meanbee_Royalmail_Test_Model_Shipping_Carrier_Royalmail_Abstract {
    /** @var Meanbee_Royalmail_Model_Shipping_Carrier_Royalmail_Specialdelivery9am50 */
    protected $_model = null;

    public function setUp() {
        parent::setUp();

        $this->_model = Mage::getModel('royalmail/shipping_carrier_royalmail_specialdelivery9am50');
    }

    public function tearDown() {
        parent::tearDown();

        $this->_model = null;
    }

    public function testNotAllowedFromFrance() {
        $this->assertNull(
            $this->_model->getCost($this->_getRateRequest(
                50,
                1.00,
                'FR'
            ))
        );
    }

    public function testMaximumBasketValue() {
        $this->assertNull(
            $this->_model->getCost(
                $this->_getRateRequest(
                    50,
                    50.01,
                    'GB'
                )
            )
        );

        $this->assertNotNull(
            $this->_model->getCost(
                $this->_getRateRequest(
                    50,
                    50.00,
                    'GB'
                )
            )
        );
    }

    public function testMinimalPrice() {
        $this->assertEquals(
            16.70,
            $this->_model->getCost(
                $this->_getRateRequest(
                    50,
                    1.00,
                    'GB'
                )
            )
        );
    }

    public function testUpperLimit() {
        $this->assertEquals(
            24.75,
            $this->_model->getCost(
                $this->_getRateRequest(
                    2000,
                    1.00,
                    'GB'
                )
            )
        );

        $this->assertNull(
            $this->_model->getCost($this->_getRateRequest(
                2001,
                1.00,
                'GB'
            ))
        );
    }
}
