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
	private $api = 'http://192.168.76.100:9200';
	/**
	 * [$organization description]
	 * @var string
	 */
	private $organization = "5e00f1b9-82c7-4190-b080-3fb8c93d123d";
	/**
	 * [$places description]
	 * @var [type]
	 */
	private $places = [
		["country" => "Canada", "state" =>"Ontario", "city" => "Toronto"],
		["country" => "Philippines", "state" =>"Manila", "city" => "Pasig"],
		["country" => "Philippines", "state" =>"Manila", "city" => "Quezon City"],
		["country" => "United States", "state" =>"California", "city" => "Los Angeles"],
		["country" => "United States", "state" =>"California", "city" => "Sacramento"],
		["country" => "Japan", "state" =>"Tokyo", "city" => "Tokyo"]
	];
	/**
	 * [$teams description]
	 * @var [type]
	 */
	private $teams = [
		['id' => '7e57d004-2b93-0e7a-b45f-5387367f91cx', 'name' => 'Technical Support'],
		['id' => '7e57d004-2b97-0e73-b45f-5387f67791cf', 'name' => 'Customer Service'],
		['id' => '7e53dd04-2b97-0e7a-b45f-53f7367791cd', 'name' => 'Retention Department']
	];
	/**
	 * [$agents description]
	 * @var [type]
	 */
	private $agents = [
		['id' => '7e57d004-2b93-0e7a-b45f-5387367791cx', 'name' => 'Paulo Marinas'],
		['id' => '7e57d004-2b97-0e73-b45f-5387367791cf', 'name' => 'Nikki Bryan'],
		['id' => '7e53dd04-2b97-0e7a-b45f-5387367791cd', 'name' => 'Efren Corpuz']
	];
	/**
	 * [$browsers description]
	 * @var [type]
	 */
	private $browsers = ['Internet Explorer', 'Firefox', 'Chrome', 'Safari', 'Opera'];
	/**
	 * @param  integer $limit
	 * @return [type]
	 */
	public function generateSessions($limit = 1) {
		$range = range(0, $limit);
		$faker = \Faker\Factory::create();
		foreach ($range as $index) {
			$place = $this->places[rand(0,5)];
			$created_at = $faker->unixTime() * 1000;
			$session_id = $faker->uuid;
			$data = [
				"wait_time"     => rand(60, 1200),
				"handling_time" => rand(60, 1200),
				"city"          => $place['city'],
				"state"         => $place['state'],
				"country"       => $place['country'],
				"browser"       => $this->browsers[rand(0, 4)],
				"created_at"    => $created_at
			];
			$this->request('sessions', $session_id, $data);
			$this->generateTeam($session_id, $data);
			$this->generateAgent($session_id, $data);
			$this->generateVisitor($session_id, $created_at);
		}
	}
	public function generateAgent($session_id, $data) {
		$agent = $this->agents[rand(0,2)];
		$data = [
			"agent_id"      => $agent['id'],
			"agent_name"    => $agent['name'],
			"session_id"    => $session_id,
			"wait_time"     => $data['wait_time'],
			"handling_time" => $data['handling_time'],
			"city"          => $data['city'],
			"state"         => $data['state'],
			"country"       => $data['country'],
			"browser"       => $data['browser'],
			"created_at"    => $data['created_at'],
		];
		$faker = \Faker\Factory::create();
		$this->request('agents', $faker->uuid, $data);
	}
	public function generateTeam($session_id, $data) {
		$team = $this->teams[rand(0,2)];
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
		];
		$faker = \Faker\Factory::create();
		$this->request('teams', $faker->uuid, $data);
	}
	/**
	 * @param  [type] $session_id
	 * @param  [type] $created_at
	 * @return [type]
	 */
	public function generateVisitor($session_id, $created_at) {
		$faker = \Faker\Factory::create();
		$data = [
			"name" => $faker->name,
			"email" => $faker->email,
			"state" => 4,
			"session_id" => $session_id,
			"created_at" => $created_at
		];
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