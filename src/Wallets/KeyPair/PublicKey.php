<?php
/**
 * This file is a part of "furqansiddiqui/bitcoin-php" package.
 * https://github.com/furqansiddiqui/bitcoin-php
 *
 * Copyright (c) 2019 Furqan A. Siddiqui <hello@furqansiddiqui.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code or visit following link:
 * https://github.com/furqansiddiqui/bitcoin-php/blob/master/LICENSE
 */

declare(strict_types=1);

namespace FurqanSiddiqui\Bitcoin\Wallets\KeyPair;

use FurqanSiddiqui\BIP32\Extend\PrivateKeyInterface;
use FurqanSiddiqui\Bitcoin\AbstractBitcoinNode;
use FurqanSiddiqui\Bitcoin\Address\P2PKH_Address;
use FurqanSiddiqui\Bitcoin\Serialize\Base58Check;
use FurqanSiddiqui\DataTypes\Base16;
use FurqanSiddiqui\DataTypes\Binary;

/**
 * Class PublicKey
 * @package FurqanSiddiqui\Bitcoin\Wallets\KeyPair
 */
class PublicKey extends \FurqanSiddiqui\BIP32\KeyPair\PublicKey
{
    /** @var AbstractBitcoinNode */
    private $node;
    /** @var null|PrivateKey */
    protected $privateKey;
    /** @var null|Base16 */
    private $hash160;

    /**
     * PublicKey constructor.
     * @param AbstractBitcoinNode $node
     * @param PrivateKey|null $keyPair
     * @param Binary|null $publicKey
     * @param Binary|null $compressed
     * @throws \FurqanSiddiqui\BIP32\Exception\PublicKeyException
     * @throws \FurqanSiddiqui\ECDSA\Exception\ECDSA_Exception
     * @throws \FurqanSiddiqui\ECDSA\Exception\GenerateVectorException
     * @throws \FurqanSiddiqui\ECDSA\Exception\MathException
     */
    public function __construct(AbstractBitcoinNode $node, ?PrivateKey $keyPair, ?Binary $publicKey = null, ?Binary $compressed = null)
    {
        $this->node = $node;
        parent::__construct($keyPair, $publicKey, $compressed);
    }

    /**
     * @param AbstractBitcoinNode $network
     * @return PublicKey
     */
    public function setBitcoinNetworkInstance(AbstractBitcoinNode $network): self
    {
        $this->node = $network;
        return $this;
    }

    /**
     * @return PrivateKey
     */
    public function privateKey(): PrivateKeyInterface
    {
        return parent::privateKey();
    }

    /**
     * @return Base16
     */
    public function hash160(): Base16
    {
        if (!$this->hash160) {
            $hash160 = $this->compressedPublicKey->clone();
            $hash160 = $hash160->hash()->sha256()
                ->hash()->ripeMd160();

            $this->hash160 = $hash160->encode()->base16();
            $this->hash160->readOnly(true);
        }

        return $this->hash160;
    }

    /**
     * @param int|null $prefix
     * @return P2PKH_Address
     * @throws \FurqanSiddiqui\Bitcoin\Exception\PaymentAddressException
     */
    public function p2pkh(?int $prefix = null): P2PKH_Address
    {
        $base58Check = Base58Check::getInstance();
        $prefix = $prefix ?? $this->node->const_p2pkh_prefix;

        $rawP2PKH = $this->hash160()->clone();
        if ($prefix && $prefix > 0) {
            $rawP2PKH->prepend(dechex($prefix));
        }

        return new P2PKH_Address($this->node, $base58Check->encode($rawP2PKH)->get(), $this->hash160);
    }
}