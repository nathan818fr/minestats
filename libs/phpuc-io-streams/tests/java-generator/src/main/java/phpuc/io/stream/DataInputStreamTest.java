package phpuc.io.stream;

import java.io.ByteArrayOutputStream;
import java.io.DataOutput;
import java.io.DataOutputStream;
import java.io.IOException;

public class DataInputStreamTest {
    public static void main(String[] args) throws IOException {
        final StringBuilder sb = new StringBuilder();
        sb.append("    // java-generator start\n\n");

        // testReadBoolean
        new DataInputStreamTest("Boolean")
                .writeBoolean(true)
                .writeBoolean(false)
                .print(sb);

        // testReadByte
        new DataInputStreamTest("Byte")
                .writeByte(0)
                .writeByte(1)
                .writeByte(-1)
                .writeByte(17)
                .writeByte(127)
                .writeByte(-128)
                .writeByte(-13)
                .print(sb);

        // testReadUnsignedByte
        new DataInputStreamTest("UnsignedByte")
                .writeByte(0)
                .writeByte(1)
                .writeByte(128)
                .writeByte(17)
                .writeByte(127)
                .writeByte(255)
                .writeByte(176)
                .print(sb);

        // testReadShort
        new DataInputStreamTest("Short")
                .writeShort(0)
                .writeShort(1)
                .writeShort(-1)
                .writeShort(1456)
                .writeShort(32767)
                .writeShort(-25056)
                .writeShort(-32768)
                .print(sb);

        // testReadInt
        new DataInputStreamTest("Int")
                .writeInt(0)
                .writeInt(1)
                .writeInt(-1)
                .writeInt(425831)
                .writeInt(2147483647)
                .writeInt(-988322)
                .writeInt(-2147483648)
                .print(sb);

        // testReadLong
        new DataInputStreamTest("Long")
                .writeLong(0L)
                .writeLong(1L)
                .writeLong(-1L)
                .writeLong(425565831L)
                .writeLong(9223372036854775807L)
                .writeLong(-988377789922L)
                .writeLong(-9223372036854775808L)
                .print(sb);

        // testReadDouble
        new DataInputStreamTest("Double")
                .writeDouble(0D)
                .writeDouble(1D)
                .writeDouble(-1D)
                .writeDouble(0.962D)
                .writeDouble(-0.155D)
                .writeDouble(Double.POSITIVE_INFINITY)
                .writeDouble(Double.NaN)
                .writeDouble(Double.NEGATIVE_INFINITY)
                .print(sb);

        // testReadFloat
        new DataInputStreamTest("Float")
                .writeFloat(0F)
                .writeFloat(1F)
                .writeFloat(-1F)
                .writeFloat(0.962F)
                .writeFloat(-0.155F)
                .writeFloat(Float.POSITIVE_INFINITY)
                .writeFloat(Float.NEGATIVE_INFINITY)
                .writeFloat(Float.NaN)
                .print(sb);

        sb.append("\n    // java-generator end\n");
        final String result = sb.toString();
        System.out.print(result);
    }

    private final String name;
    private final ByteArrayOutputStream bos = new ByteArrayOutputStream();
    private final DataOutput os = new DataOutputStream(bos);

    public DataInputStreamTest(String name) {
        this.name = name;
    }

    public DataInputStreamTest writeBoolean(boolean b) throws IOException {
        os.writeBoolean(b);
        return this;
    }

    public DataInputStreamTest writeByte(int i) throws IOException {
        os.writeByte(i);
        return this;
    }

    public DataInputStreamTest writeShort(int i) throws IOException {
        os.writeShort(i);
        return this;
    }

    public DataInputStreamTest writeChar(int i) throws IOException {
        os.writeChar(i);
        return this;
    }

    public DataInputStreamTest writeInt(int i) throws IOException {
        os.writeInt(i);
        return this;
    }

    public DataInputStreamTest writeLong(long l) throws IOException {
        os.writeLong(l);
        return this;
    }

    public DataInputStreamTest writeFloat(float v) throws IOException {
        os.writeFloat(v);
        return this;
    }

    public DataInputStreamTest writeDouble(double v) throws IOException {
        os.writeDouble(v);
        return this;
    }

    public void print(StringBuilder sb) {
        final byte[] bytes = bos.toByteArray();
        sb.append("    private static $testBuffer").append(this.name).append(" = \"");
        for (byte b : bytes) {
            sb.append("\\x");
            String bHex = Integer.toHexString(b & 0xFF);
            if (bHex.length() < 2)
                sb.append("0");
            sb.append(bHex);
        }
        sb.append("\";\n");
    }
}
