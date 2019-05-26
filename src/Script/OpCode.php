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

namespace FurqanSiddiqui\Bitcoin\Script;

use FurqanSiddiqui\DataTypes\Binary;

/**
 * Class OpCode
 * @package FurqanSiddiqui\Bitcoin\Script
 * @method self OP_0()
 * @method self OP_TRUE()
 * @method self OP_VERIFY()
 * @method self OP_RETURN()
 * @method self OP_DUP()
 * @method self OP_EQUAL()
 * @method self OP_EQUALVERIFY()
 * @method self OP_HASH160()
 * @method self OP_HASH256()
 * @method self OP_CHECKSIG()
 * @method self OP_CHECKSIGVERIFY()
 * @method self OP_CHECKMULTISIG()
 * @method self OP_CHECKMULTISIGVERIFY()
 */
class OpCode
{
    /** @var array */
    public const OP_CODES = [
        "OP_0" => 0x00,
        "OP_TRUE" => 0x51,
        "OP_IF" => 0x63,
        "OP_NOTIF" => 0x64,
        "OP_ELSE" => 0x67,
        "OP_ENDIF" => 0x68,
        "OP_VERIFY" => 0x69,
        "OP_RETURN" => 0x6a,
        "OP_DUP" => 0x76,
        "OP_EQUAL" => 0x87,
        "OP_EQUALVERIFY" => 0x88,
        "OP_1ADD" => 0x8b,
        "OP_1SUB" => 0x8c,
        "OP_ADD" => 0x93,
        "OP_SUB" => 0x94,
        "OP_RIPEMD160" => 0xa6,
        "OP_SHA1" => 0xa7,
        "OP_SHA256" => 0xa8,
        "OP_HASH160" => 0xa9,
        "OP_HASH256" => 0xaa,
        "OP_CHECKSIG" => 0xac,
        "OP_CHECKSIGVERIFY" => 0xad,
        "OP_CHECKMULTISIG" => 0xae,
        "OP_CHECKMULTISIGVERIFY" => 0xaf
    ];

    /** @var array */
    private $script;

    /**
     * OpCode constructor.
     */
    public function __construct()
    {
        $this->script = [];
    }

    /**
     * @param $name
     * @param $arguments
     * @return OpCode
     */
    public function __call($name, $arguments)
    {
        if (substr($name, 0, 3) === "OP_") {
            return $this->OP($name);
        }

        throw new \DomainException('Cannot call inaccessible method');
    }

    /**
     * @param string $op
     * @return OpCode
     */
    public function OP(string $op): self
    {
        $flag = sprintf("OP_%s", strtoupper($op));
        if (!in_array($flag, self::OP_CODES)) {
            throw new \OutOfBoundsException('Requested OP code is not registered/supported');
        }

        $this->script[] = $flag;
        return $this;
    }

    /**
     * @param Binary $data
     * @return OpCode
     */
    public function PUSHDATA(Binary $data): self
    {
        $dataLen = $data->size()->bytes();
        if ($dataLen < 1 || $dataLen > 75) {
            throw new \LengthException('PUSHDATA method can only be used for data between 1 and 75 bytes');
        }

        $this->script[] = sprintf('PUSHDATA(%d)[%s]', $dataLen, $data->encode()->base16()->hexits(false));
        return $this;
    }

    /**
     * @return Script
     */
    public function script(): Script
    {
        return new Script(implode(" ", $this->script));
    }

    /**
     * @return Script
     */
    public function getScript(): Script
    {
        return $this->script();
    }
}