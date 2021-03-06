<?php
/**
 * This file is a part of "furqansiddiqui/bitcoin-php" package.
 * https://github.com/furqansiddiqui/bitcoin-php
 *
 * Copyright (c) 2019-2020 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/furqansiddiqui/bitcoin-php/blob/master/LICENSE
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Bitcoin\Address;

use Comely\DataTypes\Buffer\Base16;
use FurqanSiddiqui\Bitcoin\AbstractBitcoinNode;
use FurqanSiddiqui\Bitcoin\Exception\PaymentAddressException;
use FurqanSiddiqui\Bitcoin\Script\Script;
use FurqanSiddiqui\Bitcoin\Serialize\Base58Check;

/**
 * Class AddressFactory
 * @package FurqanSiddiqui\Bitcoin\Address
 */
class AddressFactory
{
    /** @var AbstractBitcoinNode */
    private $node;

    /**
     * MnemonicFactory constructor.
     * @param AbstractBitcoinNode $node
     */
    public function __construct(AbstractBitcoinNode $node)
    {
        $this->node = $node;
    }

    /**
     * @param string $address
     * @return P2PKH_Address
     * @throws \FurqanSiddiqui\Bitcoin\Exception\PaymentAddressException
     */
    public function p2pkh(string $address): P2PKH_Address
    {
        return new P2PKH_Address($this->node, $address);
    }

    /**
     * @param string $address
     * @return P2SH_Address
     * @throws PaymentAddressException
     */
    public function p2sh(string $address): P2SH_Address
    {
        return new P2SH_Address($this->node, $address);
    }

    /**
     * @param string $address
     * @return PaymentAddressInterface
     * @throws PaymentAddressException
     */
    public function address(string $address): PaymentAddressInterface
    {
        $base58Check = Base58Check::getInstance();
        $decodedAddress = $base58Check->decode($address);
        $decodedAddressHexits = $decodedAddress->hexits();
        $decodedAddressPrefix = hexdec(substr($decodedAddressHexits, 0, 2));

        // P2PKH
        $p2pkhPrefix = $this->node->const_p2pkh_prefix;
        if (is_int($p2pkhPrefix) && $decodedAddressPrefix === $p2pkhPrefix) {
            return new P2PKH_Address($this->node, $address);
        }

        // P2SH
        $p2shPrefix = $this->node->const_p2sh_prefix;
        if (is_int($p2shPrefix) && $decodedAddressPrefix === $p2shPrefix) {
            return new P2SH_Address($this->node, $address);
        }

        throw new PaymentAddressException('Could not identify given address as P2PKH/P2SH');
    }

    /**
     * @param Script $scriptPubKey
     * @return PaymentAddressInterface
     * @throws PaymentAddressException
     */
    public function fromScript(Script $scriptPubKey): PaymentAddressInterface
    {
        return $this->addressFromScript($scriptPubKey);
    }

    /**
     * @param Script $scriptPubKey
     * @return PaymentAddressInterface
     * @throws PaymentAddressException
     */
    public function addressFromScript(Script $scriptPubKey): PaymentAddressInterface
    {
        $base58Check = Base58Check::getInstance();
        $scriptHex = $scriptPubKey->script()->hexits(false);
        if (preg_match('/^76a914[a-f0-9]{40}88ac$/i', $scriptHex)) {
            $hash160 = new Base16(substr($scriptHex, 6, 40));
            $hash160->prepend(dechex($this->node->const_p2pkh_prefix));
            return $this->p2pkh($base58Check->encode($hash160)->value());
        } elseif (preg_match('/^a914[a-f0-9]{40}87$/i', $scriptHex)) {
            $hash160 = new Base16(substr($scriptHex, 4, 40));
            $hash160->prepend(dechex($this->node->const_p2sh_prefix));
            return $this->p2sh($base58Check->encode($hash160)->value());
        }

        throw new PaymentAddressException('Could not identify given ScriptPubKey as P2PKH/P2SH');
    }
}