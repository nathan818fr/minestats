<?php
namespace PhpUC\IO\Stream;

interface DataInput
{
    const BIG_ENDIAN = 0;
    const LITLE_ENDIAN = 1;

    public function getEndianness();

    /**
     * Reads one input byte and returns true if that byte is nonzero, false if
     * that byte is zero.
     *
     * @return bool the boolean value read.
     * @throws EOFException
     * @throws IOException
     */
    public function readBoolean() : bool;

    /**
     * Reads and returns one input byte. The byte is treated as a signed value
     * in the range -128 through 127, inclusive.
     *
     * @return int the 8-bit value read.
     * @throws EOFException
     * @throws IOException
     */
    public function readByte() : int;

    /**
     * Reads and returns one input byte. The byte is treated as a unsigned value
     * in the range 0 through 255, inclusive.
     *
     * @return int the unsigned 8-bit value read.
     * @throws EOFException
     * @throws IOException
     */
    public function readUnsignedByte() : int;

    /**
     * Reads two input bytes and returns a short value.
     *
     * @return int the 16-bit value read.
     * @throws EOFException
     * @throws IOException
     */
    public function readShort() : int;

    /**
     * Reads two input bytes and returns an unsigned short value.
     *
     * @return int the unsigned 16-bit value read.
     * @throws EOFException
     * @throws IOException
     */
    public function readUnsignedShort() : int;

    /**
     * Reads four input bytes and returns an int value.
     *
     * @return int the int value read.
     * @throws EOFException
     * @throws IOException
     */
    public function readInt() : int;

    /**
     * Reads four input bytes and returns an unsigned int value.
     *
     * WARNING: Require a 64 bits system.
     *
     * @return int the int value read.
     * @throws EOFException
     * @throws IOException
     */
    public function readUnsignedInt() : int;

    /**
     * Reads eight input bytes and returns a long value.
     *
     * WARNING: Require a 64 bits system.
     *
     * @return int the long value read.
     * @throws EOFException
     * @throws IOException
     */
    public function readLong() : int;

    /**
     * Reads eight input bytes and returns a long value.
     *
     * WARNING: Require a 64 bits system.
     * Will not work with the biggest value because PHP does not natively
     * support unsigned numbers.
     *
     * @return int the long value read.
     * @throws EOFException
     * @throws IOException
     */
    public function readUnsignedLong() : int;

    /**
     * Reads eight input bytes and returns a double value.
     *
     * @return int the double value read.
     * @throws EOFException
     * @throws IOException
     */
    public function readDouble() : int;

    /**
     * Reads four input bytes and returns a float value.
     *
     * @return int the float value read.
     * @throws EOFException
     * @throws IOException
     */
    public function readFloat() : int;

    /**
     * Reads len bytes from an input stream.
     *
     * @param $len the number of bytes to read
     * @return string the bytes read
     * @throws EOFException
     * @throws IOException
     */
    public function readBuf($len) : string;

    /**
     * Makes an attempt to skip over n bytes of data from the input stream.
     *
     * @param int $n the number of bytes to be skipped.
     * @return int the number of bytes actually skipped.
     * @throws IOException
     */
    public function skipBytes(int $n) : int;
}