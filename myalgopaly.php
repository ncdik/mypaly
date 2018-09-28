<?php

class Point{
	private $char;
	private $outs = array();

	public function __construct($char){
		$this->char = $char;
	}
	public function getChar(){
		return $this->char;
	}
	public function __toString(){
		$str = $this->char.'|';
		foreach ($this->outs as $v) {
			$str .= ' ' . $v['jump'] . '.' . $v['to_point'] . '.' . $v['weight'] . ' |';
		}
		return $str;
	}
	public function addOut($jump, $point, $weight){
		$this->outs[$jump] = ['jump' => $jump, 'to_point' => $point, 'weight' => $weight];
	}
}

class Tree{
	private $points;
	public $jumps;

	private $jump;
	private $oldpoint;
	private $point;
	private $weight;

	private $pointsMap; //[char => pointnumber],

	private $text;

	public function __construct($text){
		$this->jump = 0;
		$this->oldpoint = 0;
		$this->point = 0;
		$this->weight = 0;
		$this->jumps = array();//[['jump' => 0, 'cur_point' => 0, 'to_point' => 0, 'weight' => 0]];
		$this->points = array();
		$this->pointsMap = array();

		$this->text = $text;
		$this->init();
	}

	private function init(){
		$this->filterText();
		$atext = str_split($this->text);
		foreach ($atext as $value) {
			$this->addPoint($value);
		}
	}

	private function filterText(){
		$this->text = strtolower($this->text);
		$this->text = str_replace(' ', '', $this->text);
	}

	private function addPoint_old($char){
		if(empty($this->points)){ //если добавляем первую точку
			$this->points[] = new Point($char);
		}
		else{
			if($this->jump > 1){
				if($this->points[$this->point-1]->getChar() !== $char){
					$this->jump++;
					$this->points[$this->point]->addOut($this->jump, $this->weight+1);
					$this->point++;
					$this->weight = $this->jump;
					$this->points[] = new Point($char);
				}
				else{ //если буква совпадает с зеркальной
					$this->jump++;
					$this->points[$this->point]->addOut($this->jump, $this->weight-1);
					$this->point--;
					$this->weight--;
				}
			}
			else{ //если добавляем вторую точку
				$this->jump++;
				$this->points[$this->point]->addOut($this->jump, $this->weight+1);
				$this->point++;
				$this->weight++;
				$this->points[] = new Point($char);
			}
		}
	}
	private function addPoint($char){
		if(empty($this->points)){//добавляем первую точку
			//добавляем в массив points
			$this->points[] = new Point($char);
			//добавляем в карту
			$this->pointsMap[$char] = 0;
		}
		else{//добавляем не первую точку
			$this->oldpoint = $this->point;
			$this->jump++;

			//проверка существования точки
			if($this->checkPoint($char)){//если точка есть
				//это та же точка?
				//if($this->points[$this->jumps[$this->jump-1]['cur_point']]->getChar() === $char){//та же точка
				if($this->points[$this->point]->getChar() === $char){//та же точка
					//weight не изменяем
					//point не изменяем
				}
				else{//это другая точка
					//если точек больше 2х
					if(count($this->points) > 2){
						//проверка на полиномность
						//$this->jumps[$this->weight]['cur_point'] === $this->jumps[$this->weight]['to_point'] || но добавляет другой баг
						if($this->points[$this->jumps[$this->weight]['cur_point']]->getChar() === $char){
							$this->weight--;
						}
						else{
							$this->weight = $this->jump;
						}
						
						$this->point = $this->pointsMap[$char];
					}
				}
			}
			else{//если точки нет
				$this->points[] = new Point($char);
				$this->pointsMap[$char] = count($this->points)-1;
				$this->weight = $this->jump;
				$this->point = count($this->points)-1;
			}

			$this->points[$this->oldpoint]->addOut($this->jump, $this->point, $this->weight);
			$this->jumps[$this->jump] = [
				'jump' => $this->jump,
				'cur_point' => $this->oldpoint,
				'to_point' => $this->point,
				'weight' => $this->weight,
			];
		}
	}

	private function checkPoint($char){
		if(isset($this->pointsMap[$char])) return true;//$this->pointMap[$char];
		else return false;
	}

	public function __toString(){
		$str = '';
		foreach ($this->points as $value) {
			$str .= $value.chr(10);
		}
		return $str;
	}

	public function polinoms(){
		$pols = array();
		foreach ($this->jumps as $key => $value) {
			if($key != $value['weight']){
				$pols[] = [
					'keyval' => $value['weight'].' - '.$key,
					'str' => substr($this->text, $value['weight'], $key-$value['weight']+1),
				];
			}
		}

		foreach ($pols as $value) {
			echo '| ' . $value['keyval'] . ' | '.$value['str'].' |'.chr(10);
		}
	}

}
echo '<pre>';

//$text = 'abbbba';
//$text = 'abba'; //bug
//$text = 'argentina manit negra';
$text = 'xyabcbazfcxaxc';
//$text = 'ABBABAABBAABABBABAABABBAABBABAABBAABABBAABBABAABABBABAABBAABABBA';
//$text = 'zabmmefemmbat'; //bug
//$text = 'zabmefembat'; //nobug
//$text = 'Name no side in Eden Im mad A maid I am Adam mine denied is one man'; //bug
//$text = 'Anyone who reads Old and Middle English literary texts will be familiar with the midbrown volumes of the EETS with the symbol of Alfreds jewel embossed on the front cover Most of the works attributed to King Alfred or to Aelfric along with some of those by bishop Wulfstan and much anonymous prose and verse from the preConquest period are to be found within the Societys three series all of the surviving medieval drama most of the Middle English romances much religious and secular prose and verse including the English works of John Gower Thomas Hoccleve and most of Caxtons prints all find their place in the publications Without EETS editions study of medieval English texts would hardly be possible As its name states EETS was begun as a club and it retains certain features of that even now It has no physical location or even office no paid staff or editors but books in the Original Series are published in the first place to satisfy subscriptions paid by individuals or institutions This means that there is need for a regular sequence of new editions normally one or two per year achieving that sequence can pose problems for the Editorial Secretary who may have too few or too many texts ready for publication at any one time Details on a separate sheet explain how individual but not institutional members can choose to take certain back volumes in place of the newly published volumes against their subscriptions On the same sheet are given details about the very advantageous discount available to individual members on all back numbers In 1970 a Supplementary Series was begun a series which only appears occasionally it currently has 24 volumes within it some of these are new editions of texts earlier appearing in the main series Again these volumes are available at publication and later at a substantial discount to members All these advantages can only be obtained through the Membership Secretary the books are sent by post they are not available through bookshops and such bookstores as carry EETS books have only a very limited selection of the many published Editors who receive no royalties or expenses and who are only very rarely commissioned by the Society are encouraged to approach the Editorial Secretary with a detailed proposal of the text they wish to suggest to the Society early in their work interest may be expressed at that point but before any text is accepted for publication the final typescript must be approved by the Council a body of some twenty scholars and then assigned a place in the printing schedule The Society now has a stylesheet to guide editors in the layout and conventions acceptable within its series No prescriptive set of editorial principles is laid down but it is usually expected that the evidence of all relevant medieval copies of the texts in question will have been considered and that the texts edited will be complete whatever their length Editions are directed at a scholarly readership rather than a popular one though they normally provide a glossary and notes no translation is provided EETS was founded in 1864 by Frederick James Furnivall with the help of Richard Morris Walter Skeat and others to bring the mass of unprinted Early English literature within the reach of students It was also intended to provide accurate texts from which the New later Oxford English Dictionary could quote the ongoing work on the revision of that Dictionary is still heavily dependent on the Societys editions as are the Middle English Dictionary and the Toronto Dictionary of Old English In 1867 an Extra Series was started intended to contain texts already printed but not in satisfactory or readily obtainable editions this series was discontinued in 1921 and from then on all the Societys editions apart from the handful in the Supplementary Series described above were listed and numbered as part of the Original Series In all the Society has now published some 475 volumes all except for a very small number mostly of editions superseded within the series are available in print The early history of the Society is only traceable in outline no details about nineteenth century membership are available and the secretarial records of the early twentieth century were largely lost during the second world war By the 1950s a very large number of the Societys editions were out of print and finances allowed for only a very limited reprinting programme Around 1970 an advantageous arrangement was agreed with an American reprint firm to make almost all the volumes available once more whilst maintaining the membership discounts Though this arrangement was superseded towards the end of the twentieth century and the cost of reprinting has reverted to the Society as a result of the effort then it has proved possible to keep the bulk of the list in print Many comparable societies with different areas of interest were founded in the nineteenth century several of them also by Furnivall not all have survived and few have produced as many valuable volumes as EETS The Societys success continues to depend very heavily on the loyalty of members and especially on the energy and devotion of a series of scholars who have been involved with the administration of the Society the amount of time and effort spent by those who over the years have filled the role of Editorial Secretary is immeasurable Plans for publications for the coming years are well in hand there are a number of important texts which should be published within the next five years At present notably because of the efforts of a series of Executive and Membership Secretaries the Societys finances are in reasonable shape but certain trends give concern to the Council The Societys continuance is dependent on two factors the first is obviously the supply of scholarly editions suitable to be included in its series the second is on the maintenance of subscriptions and sales of volumes at a level which will cover the printing and distribution costs of the new and reprinted books The normal copyright laws cover the Societys volumes All enquiries about large scale reproduction whether by photocopying or on the internet should be directed to the Executive Secretary in the first instance The Societys continued usefulness depends on its editors and on its ability to maintain its reprinting programme and that depends on those who traditionally have become members of the Society We hope you will maintain your membership and will encourage both the libraries you use and also other individuals to join Membership conveys many benefits for you and for the wider academic community concerned for the understanding of medieval texts';
//$tr = new Tree('NamenosideinEdenImmadAmaidIamAdamminedeniedisoneman');
     		    //namenosideinedenimmadamaidiamadamminedeniedisoneman
$tr = new Tree($text);
echo $tr;
//print_r($tr->jumps);
echo $text.chr(10);
$tr->polinoms();

/**
$points = array();

$points[] = new Point('x');
$points[] = new Point('y');
$points[] = new Point('a');
$points[] = new Point('y');

$points[1]->addOut([5 => 2]);
echo '<pre>';
echo $points[1];
echo '<br>';
var_dump((string)$points[1] === (string)$points[3]);
echo '<br>';
var_dump($points);
**/
//$a = new point();
//$a->char = 'xello world';
//echo $a;
