<?php
namespace PhpUC\IO\Stream;

interface DataOutput
{
    const BIG_ENDIAN = 0;
    const LITLE_ENDIAN = 1;

    public function getEndianness();

    /**
     * Writes a boolean value to this output stream.
     *
     * @param bool $boolean the boolean to be written.
     * @return void
     * @throws IOException
     */
    public function writeBoolean($boolean);

    /**
     * Writes to the output stream the eight low-order bits of the argument.
     *
     * @param int $byteValue the byte value to be written.
     * @return void
     * @throws IOException
     */
    public function writeByte($byteValue);

    /**
     * Writes to the output stream the sixteen low-order bits of the argument.
     *
     * @param int $shortValue the short value to be written.
     * @return void
     * @throws IOException
     */
    public function writeShort($shortValue);

    /**
     * Writes to the output stream the thirty two low-order bits of the argument.
     *
     * @param int $intValue the int value to be written.
     * @return void
     * @throws IOException
     */
    public function writeInt($intValue);

    /**
     * Writes to the output stream the sixty four low-order bits of the
     * argument.
     *
     * @param int $longValue the long value to be written.
     * @return void
     * @throws IOException
     */
    public function writeLong($longValue);

    /**
     * Writes a double value, which is comprised of eight bytes, to the output
     * stream.
     *
     * @param int $doubleValue the double value to be written.
     * @return void
     * @throws IOException
     */
    public function writeDouble($doubleValue);

    /**
     * Writes a float value, which is comprised of four bytes, to the output
     * stream.
     *
     * @param int $floatValue the float value to be written.
     * @return void
     * @throws IOException
     */
    public function writeFloat($floatValue);

    /**
     * Write all bytes to the output.
     *
     * @param string $buf the bytes buffer
     * @return void
     * @throws IOException
     */
    public function writeBuf($buf);
}