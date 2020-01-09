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

namespace FurqanSiddiqui\Bitcoin\Script;

use FurqanSiddiqui\Bitcoin\AbstractBitcoinNode;
use FurqanSiddiqui\Bitcoin\Address\P2SH_Address;
use FurqanSiddiqui\Bitcoin\Address\P2SH_P2WSH_Address;
use FurqanSiddiqui\Bitcoin\Wallets\KeyPair\PublicKey;

/**
 * Class MultiSigScript
 * @package FurqanSiddiqui\Bitcoin\Script
 */
class MultiSigScript
{
    /** @var AbstractBitcoinNode */
    private $network;
    /** @var int */
    private $total;
    /** @var int */
    private $req;
    /** @var array */
    private $publicKeys;
    /** @var Script */
    private $redeemScript;

    /**
     * MultiSigScript constructor.
     * @param AbstractBitcoinNode $network
     * @param int $req
     * @param PublicKey ...$publicKeys
     * @throws \FurqanSiddiqui\Bitcoin\Exception\ScriptParseException
     */
    public function __construct(AbstractBitcoinNode $network, int $req, PublicKey ...$publicKeys)
    {
        if (!$req) {
            throw new \InvalidArgumentException('Invalid required number of signatures for MultiSig script');
        }

        $this->network = $network;
        $this->total = 0;
        $this->req = $req;
        $this->publicKeys = [];
        foreach ($publicKeys as $publicKey) {
            $this->publicKeys[] = $publicKey;
            $this->total++;
        }

        if ($this->req > $this->total) {
            throw new \InvalidArgumentException('Required signatures count cannot exceed total public keys');
        }

        if ($this->req > 14 || $this->total > 14) {
            throw new \InvalidArgumentException('Too many signatures/public keys');
        }

        // Create RedeemScript
        $opCode = $this->network->script()->new();
        $opCode->OP(sprintf('OP_%d', $this->req));
        /** @var PublicKey $publicKey */
        foreach ($this->publicKeys as $publicKey) {
            $opCode->PUSHDATA($publicKey->compressed()->binary());
        }

        $opCode->OP(sprintf('OP_%d', $this->total));
        $opCode->OP_CHECKMULTISIG();
        $this->redeemScript = $opCode->script();
    }

    /**
     * @return P2SH_Address
     * @throws \FurqanSiddiqui\Bitcoin\Exception\PaymentAddressException
     */
    public function p2sh(): P2SH_Address
    {
        return $this->network->p2sh()->fromRedeemScript($this->redeemScript);
    }

    /**
     * @return P2SH_P2WSH_Address
     * @throws \FurqanSiddiqui\Bitcoin\Exception\PaymentAddressException
     * @throws \FurqanSiddiqui\Bitcoin\Exception\ScriptParseException
     */
    public function segWit(): P2SH_P2WSH_Address
    {
        $redeemScriptHash = $this->network->script()->new()
            ->OP_0()
            ->PUSHDATA($this->redeemScript->hash256()->binary())
            ->script();

        $p2sh = $this->network->p2sh()->fromRedeemScript($redeemScriptHash);
        return new P2SH_P2WSH_Address($this->network, $p2sh->address(), $p2sh->hash160(), $redeemScriptHash);
    }

    /**
     * @return P2SH_P2WSH_Address
     * @throws \FurqanSiddiqui\Bitcoin\Exception\PaymentAddressException
     * @throws \FurqanSiddiqui\Bitcoin\Exception\ScriptParseException
     */
    public function p2sh_P2WSH(): P2SH_P2WSH_Address
    {
        return $this->segWit();
    }
}
