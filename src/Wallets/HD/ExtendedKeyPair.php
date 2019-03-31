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

namespace FurqanSiddiqui\Bitcoin\Wallets\HD;

use FurqanSiddiqui\BIP32\Extend\ExtendedKeyInterface;
use FurqanSiddiqui\BIP32\Extend\PrivateKeyInterface;
use FurqanSiddiqui\BIP32\ExtendedKey;
use FurqanSiddiqui\Bitcoin\AbstractBitcoinNode;
use FurqanSiddiqui\Bitcoin\Wallets\KeyPair\PrivateKey;
use FurqanSiddiqui\DataTypes\Binary;

/**
 * Class ExtendedKeyPair
 * @package FurqanSiddiqui\Bitcoin\Wallets\HD
 */
class ExtendedKeyPair extends ExtendedKey
{
    /** @var AbstractBitcoinNode */
    private $node;

    /**
     * ExtendedKeyPair constructor.
     * @param AbstractBitcoinNode $node
     * @param Binary $seed
     * @param ExtendedKeyInterface|null $parent
     * @throws \FurqanSiddiqui\BIP32\Exception\ExtendedKeyException
     */
    public function __construct(AbstractBitcoinNode $node, Binary $seed, ?ExtendedKeyInterface $parent = null)
    {
        $this->node = $node;
        parent::__construct($seed, $parent);
    }

    /**
     * @return PrivateKeyInterface
     */
    public function privateKey(): PrivateKeyInterface
    {
        if (!$this->privateKeyInstance instanceof PrivateKey) {
            $this->privateKeyInstance = new PrivateKey($this->node, $this->privateKey, $this);
        }

        return $this->privateKeyInstance;
    }
}