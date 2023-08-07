<?php

namespace Yuxk\Helper;

class IdentityNumber
{

    public bool $isIdCard = false;
    public int $age;
    public int $sex;
    public static string $idCard;

    //城市码
    private static array $cityCode = [
        '11','12','13','14','15','21','22',
        '23','31','32','33','34','35','36',
        '37','41','42','43','44','45','46',
        '50','51','52','53','54','61','62',
        '63','64','65','71','81','82','91'
    ];

    // 加权因子
    private static array $factor = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];

    // 校验码对应值
    private static array $verifyCodeList = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];

    public function __construct(string $idCard)
    {
        self::$idCard = $idCard;
        self::check();
    }


    private function check(): bool
    {
        // 只能是18位
        if (strlen(self::$idCard) != 18) {
            return $this->isIdCard;
        }

        if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', self::$idCard)) {
            return $this->isIdCard;
        }

        if (!in_array(substr(self::$idCard, 0, 2), self::$cityCode)) {
            return $this->isIdCard;
        }

        // 取出本体码
        $idCardBase = substr(self::$idCard, 0, 17);

        // 取出校验码
        $verifyCode = substr(self::$idCard, 17, 1);

        // 根据前17位计算校验码
        $total = 0;
        for ($i = 0; $i < 17; $i++) {
            $total += substr($idCardBase, $i, 1) * self::$factor[$i];
        }

        // 取模
        $mod = $total % 11;

        // 比较校验码
        if ($verifyCode != self::$verifyCodeList[$mod]) {
            return false;
        }

        $this->isIdCard = true;

        $this->age = (int)date('Y') - (int)substr(self::$idCard, 6, 4);
        $this->sex = (int)substr(self::$idCard, 16, 1) % 2 ? 1 : 2;

        return $this->isIdCard;

    }

}
