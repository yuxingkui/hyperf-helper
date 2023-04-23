<?php

declare(strict_types=1);

namespace Yuxk\Helpers;

class ToolHelper
{
    /**
     *数字金额转换成中文大写金额的函数
     *String Int $num 要转换的小写数字或小写字符串
     *return 大写字母
     *小数位为两位
     **/
    public static function numToRmb($num): string
    {
        $c1 = "零壹贰叁肆伍陆柒捌玖";
        $c2 = "分角元拾佰仟万拾佰仟亿";
        //精确到分后面就不要了，所以只留两个小数位
        $num = round($num, 2);
        //将数字转化为整数
        $num = $num * 100;
        if (strlen((string)$num) > 10) {
            return "金额太大，请检查";
        }
        $i = 0;
        $c = "";
        while (1) {
            if ($i == 0) {
                //获取最后一位数字
                $n = substr((string)$num, strlen((string)$num) - 1, 1);
            } else {
                $n = $num % 10;
            }
            //每次将最后一位数字转化为中文
            $p1 = substr($c1, 3 * $n, 3);
            $p2 = substr($c2, 3 * $i, 3);
            if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
                $c = $p1.$p2.$c;
            } else {
                $c = $p1.$c;
            }
            $i = $i + 1;
            //去掉数字最后一位了
            $num = $num / 10;
            $num = (int)$num;
            //结束循环
            if ($num == 0) {
                break;
            }
        }
        $j = 0;
        $sLen = strlen($c);
        while ($j < $sLen) {
            //utf8一个汉字相当3个字符
            $m = substr($c, $j, 6);
            //处理数字中很多0的情况,每次循环去掉一个汉字“零”
            if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
                $left = substr($c, 0, $j);
                $right = substr($c, $j + 3);
                $c = $left.$right;
                $j = $j - 3;
                $sLen = $sLen - 3;
            }
            $j = $j + 3;
        }
        //这个是为了去掉类似23.0中最后一个“零”字
        if (substr($c, strlen($c) - 3, 3) == '零') {
            $c = substr($c, 0, strlen($c) - 3);
        }
        //将处理的汉字加上“整”
        if (empty($c)) {
            return "零元整";
        } else {
            return $c."整";
        }
    }

    /**
     * 生成code
     * @return string
     */
    public static function testCode(): string
    {
        return date('YmdHis').rand(1000, 9999);
    }

    /**
     * 格式化时间戳
     * @param string $format
     * @return string
     */
    public static function formatDate(string $format = 'Y-m-d H:i:s'): string
    {
        return date($format);
    }

    /**
     * 验证手机号
     * @param string $phone
     * @return bool
     */
    public static function isPhone(string $phone): bool
    {
        if (preg_match("/^1[3456789]{1}\d{9}$/", $phone)) {
            return true;
        }

        return false;
    }

    /**
     * 毫秒时间戳
     * @return string
     */
    public static function millisecond(): string
    {
        return (string)(int)(microtime(true) * 1000);
    }

    /**
     * 获取ip地址
     * @return string
     */
    public static function getServerIp(): string
    {
        return system("cat /etc/hosts | grep `hostname` | awk '{print $1}'") ?: "127.0.0.1";
    }
}
