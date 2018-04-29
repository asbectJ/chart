<?php
/**
 * 生产折线图
 * @author Tanjj
 * 
*/
class Chart
{
    private $width;
    private $hight;
    private $fontPath;
    private $dataSize; //数据字体大小

    public function __construct($width, $hight, $fontPath = '', $dataSize = 5)
    {
        $this->width = $width;
        $this->hight = $hight;
        $this->hight = $hight;
        $this->dataSize = $dataSize;
        $this->fontPath = $fontPath ?: dirname(__DIR__) . '/_tjj_/font/simsun.ttc';
    }

    public function drawLine($source_array, $xValue)
    {
        $this->startDrawLine($source_array, $xValue, $this->width, $this->hight, $this->fontPath);
    }

    public function startDrawLine($source_array, $xValue, $width = 1000, $height = 500, $fontPath = '')
    {
        if (!$source_array) {
            return 'error';
        }

        $blank_x = 100;
        $blank_y = 50;
        $pic = imagecreate($width + $blank_x, $height + $blank_y);
        imagecolorallocate($pic, 255, 255, 255); //背景色
        $color_1 = imagecolorallocate($pic, 30, 144, 255); //线条色
        $black = imagecolorallocate($pic, 0, 0, 0); //黑色
        $color_3 = imagecolorallocate($pic, 194, 194, 194); //灰色

        $lineColor = imagecolorallocate($pic, 0xcc, 0xcc, 0xcc); //网络虚线颜色

        //计算X轴间距：width/x轴总点数
        $xNumber = count($xValue);
        $distanceX = intval($width / $xNumber); //X轴间距

        //画虚线网格，X轴
        for ($i = 1; $i <= $xNumber; $i++) {
            $dis = $i * $distanceX;
            imagedashedline($pic, $dis, 0, $dis, $height, $lineColor);
            //imagestring($pic, 1, $dis, $height, $xValue[$i - 1], $lineColor); //X轴描述
            imagettftext($pic, 10, -20, $dis - 10, $height + 20, $color_1, $fontPath, $xValue[$i - 1]); //X轴描述,解决中文乱码
        }

        //准备画Y轴
        $maxY = 0;
        foreach ($source_array as $data) { //找出Y轴最大的数据
            $max = max($data['yData']);
            if ($maxY < $max) {
                $maxY = $max;
            }
        }

        $maxYHasNumber = strlen($maxY); //最大的数的位数
        $zero = 1;
        for ($i = 1; $i <= $maxYHasNumber - 2; $i++) {
            $zero = $zero * 10;
        }
        $secondNumber = substr($maxY, 1, 1);

        if ($secondNumber == 9) {

        }
        $maxY += $zero;

        //和Y轴最大数调整
        $beishu = ceil($maxY / $height);
        $maxY = $height * $beishu;

        $bili = round($height / $maxY, 5);

        if ($bili >= 1) { //Y轴最大数比高度小
            $bili = 1;
            $beishu = 1;
        }

        //开始画Y轴
        $j = 1;
        $yDistance = 50;
        while (1) {
            if ($j * 10 <= $height) {
                imageline($pic, $distanceX, $height - $j * $yDistance, $width, $height - $j * $yDistance, $lineColor);
                $yText = ($j * $yDistance) * $beishu;
                imagestring($pic, 5, $distanceX - 50, ($height - $j * $yDistance), $yText, $black);

            } else {break;}
            $j++;
        }
        imageline($pic, $distanceX, $height, $width, $height, $lineColor);

        foreach ($source_array as $key => $data) {

            //第一个点
            $firstY = array_shift($data['yData']);

            $startX1 = 1 * $distanceX;
            $startY1 = $height - ($firstY * $bili);
            $xCount = 1;
            $color = $this->color_change($data['color'], $pic);
            imagestring($pic, $this->dataSize, $startX1, $startY1, $firstY, $color);

            //画图例
            $tu_y = $key * 50;
            imagettftext($pic, 10, 0, $width + $blank_x - 60, 40 + $tu_y, $color, $fontPath, $data['name']);
            //imagerectangle($pic, $width + $blank_x - 60, 50 + $tu_y, $width + $blank_x - 40, 60 + $tu_y, $color);
            imagefilledrectangle($pic, $width + $blank_x - 60, 50 + $tu_y, $width + $blank_x - 40, 60 + $tu_y, $color);

            foreach ($data['yData'] as $key => $y) {
                $drwaX = (++$xCount) * $distanceX;
                $drwaY = $height - ($y * $bili);

                imageline($pic, $startX1, $startY1, $drwaX, $drwaY, $color);
                imagestring($pic, $this->dataSize, $drwaX, $drwaY, $y, $color);

                $startX1 = $drwaX;
                $startY1 = $drwaY;
            }

        }
        header("Content-type:image/png");
        imagepng($pic);
        imagedestroy($pic);

    }

    // 颜色转换
    private function color_change($col, $img)
    {
        $col = substr($col, 1, strlen($col) - 1);
        $red = hexdec(substr($col, 0, 2));
        $green = hexdec(substr($col, 2, 2));
        $blue = hexdec(substr($col, 4, 2));
        return imagecolorallocate($img, $red, $green, $blue);
    }

}


//测试效果，数据格式为：
// $source_array = [

//     [
//         'name' => '数据1',
//         'color' => '#058DC7',
//         'yData' => [1432523, 100, 60, 80, 100, 30, 200, 300, 120, 340, 300, 200, 60, 80, 100, 30, 200, 300, 120, 340],
//     ],

//     [
//         'name' => '数据2',
//         'color' => '#ED561B',
//         'yData' => [1345, 100, 60, 80, 1332523, 23423, 200, 300, 120, 340, 300, 200, 60, 80, 100, 30, 200, 300, 120, 340],
//     ],
// ];
// $xValue = ['测试一', '测试2'];//x轴


$color = array('#058DC7', '#50B432', '#ED561B', '#DDDF00', '#24CBE5', '#64E572', '#FF9655', '#FFF263', '#6AF9C4');
$source_array = [];
for ($i = 1; $i <= 4; $i++) {
    $data = [];
    for ($j = 1; $j <= 10; $j++) {
        $data[] = rand(500, 1115000);
    }
    $row['color'] = $color[$i];
    $row['yData'] = $data;
    $row['name'] = "数据{$i}";
    array_push($source_array, $row);
}

$xValue = ['测试一', '测试2', '测试3', '测试4', '测试5', '测试6', '测试7', '测试8', '测试9', '测试10'];
$chart = new Chart(1000, 500, '', 5);
$chart->drawLine($source_array, $xValue);
