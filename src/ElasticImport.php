<?php namespace Jdecano;
/**
 * ElasticImport
 * @author John Dave Decano <johndavedecano@gmail.com>
 */
class ElasticImport {
	/**
	 * [$api description]
	 * @var string
	 */
	private $api = 'http://localhost:9200';
	/**
	 * [$organization description]
	 * @var string
	 */
	private $organization = "5e00f1b9-82c7-4190-b080-3fb8c93d123d";
	/**
	 * [$devices description]
	 * @var [type]
	 */
	private $devices = ['Other', 'Mobile'];
	/**
	 * [$os description]
	 * @var [type]
	 */
	private $os = ['Linux', 'Windows', 'Android'];
	/**
	 * [$engines description]
	 * @var [type]
	 */
	private $engines = ['AppleWebKit','WebKit','Blink','Trident','Gecko','KHTML','NetFront','Edge'];
	/**
	 * [$places description]
	 * @var [type]
	 */
	private $places = [
		["country" => "CA", "state" =>"Ontario", "city" => "Toronto"],
		["country" => "PH", "state" =>"Manila", "city" => "Pasig"],
		["country" => "PH", "state" =>"Manila", "city" => "Quezon City"],
		["country" => "US", "state" =>"California", "city" => "Los Angeles"],
		["country" => "US", "state" =>"California", "city" => "Sacramento"],
		["country" => "JP", "state" =>"Tokyo", "city" => "Tokyo"]
	];
	/**
	 * [$teams description]
	 * @var [type]
	 */
	private $teams = [
		['id' => 'team1', 'name' => 'Technical Team'],
		['id' => '7e53dd04-2b97-0e7a-b45f-53f7367791cd', 'name' => 'Retention Department']
	];
	/**
	 * [$agents description]
	 * @var [type]
	 */
	private $agents = [
		['id' => '28fd3cf4-cde7-4550-a384-dc3415ee23e7', 'name' => 'Paulo Marinas'],
		['id' => '89bbacad-e1d7-4b54-9b25-f4470152a2b2', 'name' => 'Nikki Bryan'],
		['id' => 'b11b92c6-cdb8-488b-925c-0a0651b1b5b3', 'name' => 'Dave Decano']
	];
	/**
	 * [$browsers description]
	 * @var [type]
	 */
	private $browsers = ['Internet Explorer', 'Firefox', 'Chrome', 'Safari', 'Opera'];
	/**
	 * @param  string $host
	 * @return mixed
	 */
	public function setHost($host = 'http://localhost:9200') {
		$this->api = $host;
	}
	/**
	 * @param  string $contents
	 * @return mixed
	 */
	public function purge() {
		$url = $this->api.'/_flush';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response  = curl_exec($ch);
		curl_close($ch);
	}
	/**
	 * @param  string $contents
	 * @return mixed
	 */
	public function generateMappings($contents) {
		$url = $this->api.'/'.$this->organization;
		$data_json = $contents;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data_json)));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response  = curl_exec($ch);
		curl_close($ch);
	}
	/**
	 * @param  integer $limit
	 * @return [type]
	 */
	public function generateSessions($limit = 1) {
		$range = range(0, $limit);
		$faker = \Faker\Factory::create();
		$dates = range(1, 30);
		foreach ($range as $index) {
			$place = $this->places[rand(0,5)];
			$created_at = strtotime("now - ".$dates[rand(0,29)]."days") * 1000;
			$session_id = $faker->uuid;
			$rating = rand(0,3);
			$team = $this->teams[rand(0,1)];
			$agent = $this->agents[rand(0,2)];
			$ua = [
				"os"            => $this->os[rand(0,2)],
				"device"        => $this->devices[rand(0,1)],
				'engine'        => $this->engines[rand(0,7)],
				"browser"       => $this->browsers[rand(0, 4)],
			];
			$geoip = [
				"city"          => $place['city'],
				"state"         => $place['state'],
				"country"       => $place['country'],
			];
			$data = [
				"wait_time"     => rand(60, 1200),
				"handling_time" => rand(60, 1200),
				"rating"        => $rating,
				"created_at"    => $created_at
			];
			$data = array_merge($data, $ua);
			$data = array_merge($data, $geoip);
			$this->request('sessions', $session_id, $data);
			$this->generateTeam($session_id, $data, $rating, $team);
			$this->generateAgent($session_id, $data, $rating, $team, $agent);
			$this->generateVisitor($session_id, $created_at, $rating, $geoip, $ua, $agent);
		}
	}
	public function generateAgent($session_id, $data, $rating, $team, $agent) {
		$data = [
			"team_id"       => $team['id'],
			"team_name"     => $team['name'],
			"agent_id"      => $agent['id'],
			"agent_name"    => $agent['name'],
			"avatar"        => "",
			"session_id"    => $session_id,
			"wait_time"     => $data['wait_time'],
			"handling_time" => $data['handling_time'],
			"city"          => $data['city'],
			"state"         => $data['state'],
			"country"       => $data['country'],
			"browser"       => $data['browser'],
			"created_at"    => $data['created_at'],
			"rating"        => $rating,
		];
		$faker = \Faker\Factory::create();
		$this->request('agents', $faker->uuid, $data);
	}
	public function generateTeam($session_id, $data, $rating, $team) {
		$data = [
			"team_id"       => $team['id'],
			"team_name"     => $team['name'],
			"session_id"    => $session_id,
			"wait_time"     => $data['wait_time'],
			"handling_time" => $data['handling_time'],
			"city"          => $data['city'],
			"state"         => $data['state'],
			"country"       => $data['country'],
			"browser"       => $data['browser'],
			"created_at"    => $data['created_at'],
			"rating"        => $rating,
		];
		$faker = \Faker\Factory::create();
		$this->request('teams', $faker->uuid, $data);
	}
	/**
	 * @param  [type] $session_id
	 * @param  [type] $created_at
	 * @return [type]
	 */
	public function generateVisitor($session_id, $created_at, $rating, $geoip,  $ua, $agent) {
		$faker = \Faker\Factory::create();
		$name = $faker->name;
		$data = [
			"agent_id"      => $agent['id'],
			"agent_name"    => $agent['name'],
			"name"       => $name,
			"avatar"     => "",
			"initial"    => strtoupper($name[0]),
			"email"      => $faker->email,
			"phone"      => $faker->phoneNumber,
			"visitor_state"  => 4,
			"session_id" => $session_id,
			"created_at" => $created_at,
			"ip_address" => "203.82.34.242",
			"last_message" => "Hi Arthur, I'm looking for a blue shirt",
			"last_message_at" => $created_at - (86400 * 1000),
			"rating"     => $rating,
			"longitude"  => $faker->longitude,
			"latitude"   => $faker->latitude,
			"zipcode"    => $faker->postcode
		];
		$data = array_merge($data, $ua);
		$data = array_merge($data, $geoip);
		$this->request('visitor', $faker->uuid, $data);
	}
	/**
	 * @param  [type] $type
	 * @param  [type] $id
	 * @param  [type] $data
	 * @return [type]
	 */
	private function request($type, $id, $data) {
		$url = $this->api.'/'.$this->organization.'/'.$type.'/'.$id;
		$data_json = json_encode($data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data_json)));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response  = curl_exec($ch);
		curl_close($ch);
	}
}