<?php


require_once 'vendor/autoload.php';

class GenerateTables {

  private $faker;
  private $doctors = [];
  private $prescriptions = [];
  private $drugs = [];
  private $pharmacists = [];
  private $patients = [];

  public function __construct()
  {
    $this->faker = Faker\Factory::create('en_GB');
    $this->generateDoctors(5);
    $this->generatePharmacists(2);
    $this->generatePatient(60);    
    $this->createSurgery();
    $this->generateDrugs(10);
    $this->generatePrescriptions(35);
  }

  private function generateDoctors($num = 1)
  {
    $docs = [];
    for($i =0; $i < $num; $i++) {
      $doc = [];
      $doc['_id'] = $this->faker->uuid();
      $doc['name'] = 'Dr. '. $this->faker->name();
      $doc['room'] = 'Room ' . ($i + 1);
      $docs[] = $doc;
    }
    $this->doctors = $docs;
    $this->createJsonFile('doctors', $docs);
  }

  private function generatePharmacists($num = 1)
  {
    $pharmacists = [];
    for($i =0; $i < $num; $i++) {
      $pharmacist = [];
      $pharmacist['_id'] = $this->faker->uuid();
      $pharmacist['name'] = $this->faker->name();
      $pharmacists[] = $pharmacist;
    }
    $this->pharmacists = $pharmacists;
    $this->createJsonFile('pharmacist', $pharmacists);
  }

  private function generatePatient($num = 1)
  {
    $patients = [];
    for($i =0; $i < $num; $i++) {
      $patient = [];
      $patient['nhs_id'] = $this->faker->numberBetween(100000, 200000);
      $patient['name'] = $this->faker->name();
      $patient['doctor'] = $this->doctors[array_rand($this->doctors,1)]['_id'];
      $patients[] = $patient;
    }
    $this->patients = $patients;
    $this->createJsonFile('patients', $patients);
  }

  private function createSurgery()
  {
    $surgery = [];

    // doctors
    foreach($this->doctors as $dk => $dv) {
      $surgery[] = [
        'doctor_id' => $dv['_id'],
        'type' => 'doctor'
      ];
    }

    // pharmacists
    foreach($this->pharmacists as $pk => $pv) {
      $surgery[] = [
        'pharmacist_id' => $pv['_id'],
        'type' => 'pharmacist'
      ];
    }

    // patient
    foreach($this->patients as $paK => $paV) {
      $surgery[] = [
        'patient_id' => $paV['nhs_id'],
        'type' => 'patient'
      ];
    }

    $this->createJsonFile('surgery', $surgery);
  }

  private function generateDrugs($num = 1)
  {
    $drugs = [];
    for($i =0; $i < $num; $i++) {
      $drug = [];
      $drug['_id'] = $this->faker->uuid();
      $drug['drug_name'] = $this->faker->words(3, true);

      $rand = rand(1,3);
      for ($j=0; $j < $rand; $j++) { 

        $random_date = (array)$this->faker->dateTimeBetween('-1 week', '+10 week');
        $random_date_parts = explode(' ', $random_date['date']);
        $random_date = $random_date_parts[0];

        $drug['batches'][] = [
          'batch_no' => $this->faker->numberBetween(10, 40),
          'expiry_date' => $random_date
        ];
      }
      $drugs[] = $drug;
    }
    $this->drugs = $drugs;
    $this->createJsonFile('drugs', $drugs);
  }

  private function generatePrescriptions($num = 1)
  {
    $prescriptions = [];
    for($i =0; $i < $num; $i++) {

      $random_patient = $this->patients[array_rand($this->patients,1)];

      $prescription = [];
      $prescription['_id'] = $this->faker->uuid();
      $prescription['nhs_id'] = $random_patient['nhs_id'];
      $prescription['full_name'] = $random_patient['name'];;
      $prescription['doctor_id'] = $random_patient['doctor'];
      $prescription['pharmasist_id'] = $this->pharmacists[array_rand($this->pharmacists,1)]['_id'];

      $random_drug = $this->drugs[array_rand($this->drugs,1)];
      $number_of_batches = count($random_drug['batches']);
      $random_batch_number = rand(0, ($number_of_batches - 1));
      $random_drug_batch = $random_drug['batches'][$random_batch_number];

      $prescription['drugs'] = [
        'drug' => $random_drug['_id'],
        'expiry_date' => $random_drug_batch['expiry_date'],
        'batch_no' => $random_drug_batch['batch_no']
      ];      

      $prescriptions[] = $prescription;
    }
    $this->prescriptions = $prescriptions;
    $this->createJsonFile('prescriptions', $prescriptions);
  }

  private function createJsonFile($name, $data)
  {
    file_put_contents("mongofiles/$name.json", json_encode($data,JSON_PRETTY_PRINT));
  }

}

(new GenerateTables());