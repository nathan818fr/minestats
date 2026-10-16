<?php
namespace MinecraftPinger;

use PhpUC\IO\Stream\DataInput;
use PhpUC\IO\Stream\DataOutput;

/**
 * Minecraft protocol helper.
 *
 * @link http://wiki.vg/Protocol
 */
class ProtocolHelper
{
    public static function writeVarint(DataOutput $os, $value)
    {
        // < 128 was stored on single byte
        if ($value < 128) {
            $os->writeByte($value);
        } else {
            $bytes = [];
            while ($value > 0) {
                $bytes[] = 0x80 | ($value & 0x7F);
                $value >>= 7;
            }
            $bytes[count($bytes) - 1] &= 0x7F;

            foreach ($bytes as $byte) {
                $os->writeByte($byte);
            }
        }
    }

    public static function readVarint(DataInput $is)
    {
        $result = 0;
        $numRead = 0;
        do {
            $read = $is->readByte();
            $value = ($read & 0x7F);
            $result |= ($value << (7 * $numRead));

            if (++$numRead > 5) {
                throw new \RuntimeException("VarInt is too big");
            }
        } while (($read & 0x80) != 0);

        return $result;
    }

    public static function writeString(DataOutput $os, $str)
    {
        $bytesLen = strlen($str);
        self::writeVarint($os, $bytesLen);
        $os->writeBuf($str);
    }

    public static function readString(DataInput $is, $sizeLimit = null)
    {
        $bytesLen = self::readVarint($is);
        if ($sizeLimit !== null && $bytesLen > $sizeLimit) {
            throw new \RuntimeException('String response is too large!');
        }

        return $is->readBuf($bytesLen);
    }
}