<?php

namespace Database\Seeders;

use App\Models\Municipio;
use Illuminate\Database\Seeder;
use Database\Seeders\Concerns\TruncatesTables;

class MunicipioSeeder extends Seeder
{
	use TruncatesTables;

	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		$this->truncateModel(Municipio::class);

		Municipio::updateOrCreate(
			['municipio' => 'El Retorno', 'departamento_id' => 95],
			['status' => 1]);
		Municipio::updateOrCreate(
			['municipio' => 'Calamar', 'departamento_id' => 95],
			['status' => 1]);
		Municipio::updateOrCreate(
			['municipio' => 'Mapiripan', 'departamento_id' => 50],
			['status' => 1]);
		Municipio::updateOrCreate(
			['municipio' => 'Miraflores', 'departamento_id' => 95],
			['status' => 1]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Concordia', 'departamento_id' => 50],
			['status' => 1]);
		Municipio::updateOrCreate(
			['municipio' => 'San José del Guaviare', 'departamento_id' => 95],
			['status' => 1]);

		Municipio::updateOrCreate(
			['municipio' => 'Abriaquí', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Acacías', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Acandí', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Acevedo', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Achí', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Agrado', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Agua de Dios', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Aguachica', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Aguada', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Aguadas', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Aguazul', 'departamento_id' => 85],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Agustín Codazzi', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Aipe', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Albania', 'departamento_id' => 18],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Albania', 'departamento_id' => 44],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Albania', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Albán', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Albán', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Alcalá', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Alejandria', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Algarrobo', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Algeciras', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Almaguer', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Almeida', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Alpujarra', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Altamira', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Alto Baudó (Pie de Pato)', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Altos del Rosario', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Alvarado', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Amagá', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Amalfi', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ambalema', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Anapoima', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ancuya', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Andalucía', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Andes', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Angelópolis', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Angostura', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Anolaima', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Anorí', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Anserma', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ansermanuevo', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Anzoátegui', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Anzá', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Apartadó', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Apulo', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Apía', 'departamento_id' => 66],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Aquitania', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Aracataca', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Aranzazu', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Aratoca', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Arauca', 'departamento_id' => 81],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Arauquita', 'departamento_id' => 81],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Arbeláez', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Arboleda (Berruecos)', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Arboledas', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Arboletes', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Arcabuco', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Arenal', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Argelia', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Argelia', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Argelia', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ariguaní (El Difícil)', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Arjona', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Armenia', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Armenia', 'departamento_id' => 63],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Armero (Guayabal)', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Arroyohondo', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Astrea', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ataco', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Atrato (Yuto)', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ayapel', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Bagadó', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Bahía Solano (Mútis)', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Bajo Baudó (Pizarro)', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Balboa', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Balboa', 'departamento_id' => 66],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Baranoa', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Baraya', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Barbacoas', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Barbosa', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Barbosa', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Barichara', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Barranca de Upía', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Barrancabermeja', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Barrancas', 'departamento_id' => 44],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Barranco de Loba', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Barranquilla', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Becerríl', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Belalcázar', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Bello', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Belmira', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Beltrán', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Belén', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Belén', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Belén de Bajirá', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Belén de Umbría', 'departamento_id' => 66],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Belén de los Andaquíes', 'departamento_id' => 18],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Berbeo', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Betania', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Beteitiva', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Betulia', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Betulia', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Bituima', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Boavita', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Bochalema', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Bogotá D.C.', 'departamento_id' => 11],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Bojacá', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Bojayá (Bellavista)', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Bolívar', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Bolívar', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Bolívar', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Bolívar', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Bosconia', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Boyacá', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Briceño', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Briceño', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Bucaramanga', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Bucarasica', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Buenaventura', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Buenavista', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Buenavista', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Buenavista', 'departamento_id' => 63],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Buenavista', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Buenos Aires', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Buesaco', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Buga', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Bugalagrande', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Burítica', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Busbanza', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cabrera', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cabrera', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cabuyaro', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cachipay', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Caicedo', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Caicedonia', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Caimito', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cajamarca', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cajibío', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cajicá', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Calamar', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Calarcá', 'departamento_id' => 63],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Caldas', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Caldas', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Caldono', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'California', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Calima (Darién)', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Caloto', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Calí', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Campamento', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Campo de la Cruz', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Campoalegre', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Campohermoso', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Canalete', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Candelaria', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Candelaria', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cantagallo', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cantón de San Pablo', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Caparrapí', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Capitanejo', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Caracolí', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Caramanta', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Carcasí', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Carepa', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Carmen de Apicalá', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Carmen de Carupa', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Carmen de Viboral', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Carmen del Darién (CURBARADÓ)', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Carolina', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cartagena', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cartagena del Chairá', 'departamento_id' => 18],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cartago', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Carurú', 'departamento_id' => 97],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Casabianca', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Castilla la Nueva', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Caucasia', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cañasgordas', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cepita', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cereté', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cerinza', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cerrito', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cerro San Antonio', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chachaguí', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chaguaní', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chalán', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chaparral', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Charalá', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Charta', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chigorodó', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chima', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chimichagua', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chimá', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chinavita', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chinchiná', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chinácota', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chinú', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chipaque', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chipatá', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chiquinquirá', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chiriguaná', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chiscas', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chita', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chitagá', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chitaraque', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chivatá', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chivolo', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Choachí', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chocontá', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chámeza', 'departamento_id' => 85],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chía', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chíquiza', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Chívor', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cicuco', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cimitarra', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Circasia', 'departamento_id' => 63],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cisneros', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ciénaga', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ciénaga', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ciénaga de Oro', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Clemencia', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cocorná', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Coello', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cogua', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Colombia', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Colosó (Ricaurte)', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Colón', 'departamento_id' => 86],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Colón (Génova)', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Concepción', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Concepción', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Concordia', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Concordia', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Condoto', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Confines', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Consaca', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Contadero', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Contratación', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Convención', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Copacabana', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Coper', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cordobá', 'departamento_id' => 63],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Corinto', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Coromoro', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Corozal', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Corrales', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cota', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cotorra', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Covarachía', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Coveñas', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Coyaima', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cravo Norte', 'departamento_id' => 81],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cuaspud (Carlosama)', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cubarral', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cubará', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cucaita', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cucunubá', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cucutilla', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cuitiva', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cumaral', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cumaribo', 'departamento_id' => 99],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cumbal', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cumbitara', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cunday', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Curillo', 'departamento_id' => 18],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Curití', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Curumaní', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cáceres', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cáchira', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cácota', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cáqueza', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cértegui', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cómbita', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Córdoba', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Córdoba', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Cúcuta', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Dabeiba', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Dagua', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Dibulla', 'departamento_id' => 44],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Distracción', 'departamento_id' => 44],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Dolores', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Don Matías', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Dos Quebradas', 'departamento_id' => 66],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Duitama', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Durania', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ebéjico', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Bagre', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Banco', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Cairo', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Calvario', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Carmen', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Carmen', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Carmen de Atrato', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Carmen de Bolívar', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Castillo', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Cerrito', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Charco', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Cocuy', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Colegio', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Copey', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Doncello', 'departamento_id' => 18],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Dorado', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Dovio', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Espino', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Guacamayo', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Guamo', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Molino', 'departamento_id' => 44],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Paso', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Paujil', 'departamento_id' => 18],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Peñol', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Peñon', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Peñon', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Peñón', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Piñon', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Playón', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Retén', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Roble', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Rosal', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Rosario', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Tablón de Gómez', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Tambo', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Tambo', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Tarra', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Zulia', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'El Águila', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Elías', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Encino', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Enciso', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Entrerríos', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Envigado', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Espinal', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Facatativá', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Falan', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Filadelfia', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Filandia', 'departamento_id' => 63],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Firavitoba', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Flandes', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Florencia', 'departamento_id' => 18],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Florencia', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Floresta', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Florida', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Floridablanca', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Florián', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Fonseca', 'departamento_id' => 44],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Fortúl', 'departamento_id' => 81],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Fosca', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Francisco Pizarro', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Fredonia', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Fresno', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Frontino', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Fuente de Oro', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Fundación', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Funes', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Funza', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Fusagasugá', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Fómeque', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Fúquene', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Gachalá', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Gachancipá', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Gachantivá', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Gachetá', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Galapa', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Galeras (Nueva Granada)', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Galán', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Gama', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Gamarra', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Garagoa', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Garzón', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Gigante', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ginebra', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Giraldo', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Girardot', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Girardota', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Girón', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Gonzalez', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Gramalote', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Granada', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Granada', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Granada', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guaca', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guacamayas', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guacarí', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guachavés', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guachené', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guachetá', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guachucal', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guadalupe', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guadalupe', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guadalupe', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guaduas', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guaitarilla', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Gualmatán', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guamal', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guamal', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guamo', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guapota', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guapí', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guaranda', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guarne', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guasca', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guatapé', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guataquí', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guatavita', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guateque', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guavatá', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guayabal de Siquima', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guayabetal', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guayatá', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guepsa', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guicán', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Gutiérrez', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Guática', 'departamento_id' => 66],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Gámbita', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Gámeza', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Génova', 'departamento_id' => 63],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Gómez Plata', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Hacarí', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Hatillo de Loba', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Hato', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Hato Corozal', 'departamento_id' => 85],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Hatonuevo', 'departamento_id' => 44],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Heliconia', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Herrán', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Herveo', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Hispania', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Hobo', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Honda', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ibagué', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Icononzo', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Iles', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Imúes', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Inzá', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Inírida', 'departamento_id' => 94],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ipiales', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Isnos', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Istmina', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Itagüí', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ituango', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Izá', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Jambaló', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Jamundí', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Jardín', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Jenesano', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Jericó', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Jericó', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Jerusalén', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Jesús María', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Jordán', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Juan de Acosta', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Junín', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Juradó', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Apartada y La Frontera', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Argentina', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Belleza', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Calera', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Capilla', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Ceja', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Celia', 'departamento_id' => 66],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Cruz', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Cumbre', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Dorada', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Esperanza', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Estrella', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Florida', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Gloria', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Jagua de Ibirico', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Jagua del Pilar', 'departamento_id' => 44],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Llanada', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Macarena', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Merced', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Mesa', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Montañita', 'departamento_id' => 18],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Palma', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Paz', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Paz (Robles)', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Peña', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Pintada', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Plata', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Playa', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Primavera', 'departamento_id' => 99],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Salina', 'departamento_id' => 85],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Sierra', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Tebaida', 'departamento_id' => 63],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Tola', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Unión', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Unión', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Unión', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Unión', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Uvita', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Vega', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Vega', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Victoria', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Victoria', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Victoria', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'La Virginia', 'departamento_id' => 66],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Labateca', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Labranzagrande', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Landázuri', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Lebrija', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Leiva', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Lejanías', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Lenguazaque', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Leticia', 'departamento_id' => 91],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Liborina', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Linares', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Lloró', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Lorica', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Los Córdobas', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Los Palmitos', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Los Patios', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Los Santos', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Lourdes', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Luruaco', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Lérida', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Líbano', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'López (Micay)', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Macanal', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Macaravita', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Maceo', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Machetá', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Madrid', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Magangué', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Magüi (Payán)', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Mahates', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Maicao', 'departamento_id' => 44],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Majagual', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Malambo', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Mallama (Piedrancha)', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Manatí', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Manaure', 'departamento_id' => 44],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Manaure Balcón del Cesar', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Manizales', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Manta', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Manzanares', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Maní', 'departamento_id' => 85],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Margarita', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Marinilla', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Maripí', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Mariquita', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Marmato', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Marquetalia', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Marsella', 'departamento_id' => 66],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Marulanda', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'María la Baja', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Matanza', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Medellín', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Medina', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Medio Atrato', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Medio Baudó', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Medio San Juan (ANDAGOYA)', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Melgar', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Mercaderes', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Mesetas', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Milán', 'departamento_id' => 18],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Miraflores', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Miranda', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Mistrató', 'departamento_id' => 66],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Mitú', 'departamento_id' => 97],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Mocoa', 'departamento_id' => 86],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Mogotes', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Molagavita', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Momil', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Mompós', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Mongua', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Monguí', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Moniquirá', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Montebello', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Montecristo', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Montelíbano', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Montenegro', 'departamento_id' => 63],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Monteria', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Monterrey', 'departamento_id' => 85],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Morales', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Morales', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Morelia', 'departamento_id' => 18],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Morroa', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Mosquera', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Mosquera', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Motavita', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Moñitos', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Murillo', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Murindó', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Mutatá', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Mutiscua', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Muzo', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Málaga', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Nariño', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Nariño', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Nariño', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Natagaima', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Nechí', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Necoclí', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Neira', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Neiva', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Nemocón', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Nilo', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Nimaima', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Nobsa', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Nocaima', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Norcasia', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Norosí', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Novita', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Nueva Granada', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Nuevo Colón', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Nunchía', 'departamento_id' => 85],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Nuquí', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Nátaga', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Obando', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ocamonte', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ocaña', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Oiba', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Oicatá', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Olaya', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Olaya Herrera', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Onzaga', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Oporapa', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Orito', 'departamento_id' => 86],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Orocué', 'departamento_id' => 85],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ortega', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ospina', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Otanche', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ovejas', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pachavita', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pacho', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Padilla', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Paicol', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pailitas', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Paime', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Paipa', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pajarito', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Palermo', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Palestina', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Palestina', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Palmar', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Palmar de Varela', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Palmas del Socorro', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Palmira', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Palmito', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Palocabildo', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pamplona', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pamplonita', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pandi', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Panqueba', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Paratebueno', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pasca', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Patía (El Bordo)', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pauna', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Paya', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Paz de Ariporo', 'departamento_id' => 85],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Paz de Río', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pedraza', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pelaya', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pensilvania', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Peque', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pereira', 'departamento_id' => 66],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pesca', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Peñol', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Piamonte', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pie de Cuesta', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Piedras', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Piendamó', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pijao', 'departamento_id' => 63],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pijiño', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pinchote', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pinillos', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Piojo', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pisva', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pital', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pitalito', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pivijay', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Planadas', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Planeta Rica', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Plato', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Policarpa', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Polonuevo', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ponedera', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Popayán', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pore', 'departamento_id' => 85],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Potosí', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pradera', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Prado', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Providencia', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Providencia', 'departamento_id' => 88],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pueblo Bello', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pueblo Nuevo', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pueblo Rico', 'departamento_id' => 66],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pueblorrico', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puebloviejo', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puente Nacional', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerres', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Asís', 'departamento_id' => 86],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Berrío', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Boyacá', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Caicedo', 'departamento_id' => 86],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Carreño', 'departamento_id' => 99],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Colombia', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Escondido', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Gaitán', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Guzmán', 'departamento_id' => 86],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Leguízamo', 'departamento_id' => 86],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Libertador', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Lleras', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto López', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Nare', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Nariño', 'departamento_id' => 91],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Parra', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Rico', 'departamento_id' => 18],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Rico', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Rondón', 'departamento_id' => 81],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Salgar', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Santander', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Tejada', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Triunfo', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puerto Wilches', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pulí', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pupiales', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Puracé (Coconuco)', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Purificación', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Purísima', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Pácora', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Páez', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Páez (Belalcazar)', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Páramo', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Quebradanegra', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Quetame', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Quibdó', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Quimbaya', 'departamento_id' => 63],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Quinchía', 'departamento_id' => 66],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Quipama', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Quipile', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ragonvalia', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ramiriquí', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Recetor', 'departamento_id' => 85],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Regidor', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Remedios', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Remolino', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Repelón', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Restrepo', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Restrepo', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Retiro', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ricaurte', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ricaurte', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Rio Negro', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Rioblanco', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Riofrío', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Riohacha', 'departamento_id' => 44],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Risaralda', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Rivera', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Roberto Payán', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Roldanillo', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Roncesvalles', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Rondón', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Rosas', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Rovira', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ráquira', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Río Iró', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Río Quito', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Río Sucio', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Río Viejo', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Río de oro', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ríonegro', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ríosucio', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sabana de Torres', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sabanagrande', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sabanalarga', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sabanalarga', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sabanalarga', 'departamento_id' => 85],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sabanas de San Angel (SAN ANGEL)', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sabaneta', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Saboyá', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sahagún', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Saladoblanco', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Salamina', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Salamina', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Salazar', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Saldaña', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Salento', 'departamento_id' => 63],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Salgar', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Samacá', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Samaniego', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Samaná', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sampués', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Agustín', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Alberto', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Andrés', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Andrés Sotavento', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Andrés de Cuerquía', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Antero', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Antonio', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Antonio de Tequendama', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Benito', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Benito Abad', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Bernardo', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Bernardo', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Bernardo del Viento', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Calixto', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Carlos', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Carlos', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Carlos de Guaroa', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Cayetano', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Cayetano', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Cristobal', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Diego', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Eduardo', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Estanislao', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Fernando', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Francisco', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Francisco', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Francisco', 'departamento_id' => 86],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Gíl', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Jacinto', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Jacinto del Cauca', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Jerónimo', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Joaquín', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San José', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San José de Miranda', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San José de Montaña', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San José de Pare', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San José de Uré', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San José del Fragua', 'departamento_id' => 18],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San José del Palmar', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Juan de Arama', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Juan de Betulia', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Juan de Nepomuceno', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Juan de Pasto', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Juan de Río Seco', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Juan de Urabá', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Juan del Cesar', 'departamento_id' => 44],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Juanito', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Lorenzo', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Luis', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Luís', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Luís de Gaceno', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Luís de Palenque', 'departamento_id' => 85],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Marcos', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Martín', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Martín', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Martín de Loba', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Mateo', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Miguel', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Miguel', 'departamento_id' => 86],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Miguel de Sema', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Onofre', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Pablo', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Pablo', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Pablo de Borbur', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Pedro', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Pedro', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Pedro', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Pedro de Cartago', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Pedro de Urabá', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Pelayo', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Rafael', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Roque', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Sebastián', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Sebastián de Buenavista', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Vicente', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Vicente del Caguán', 'departamento_id' => 18],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Vicente del Chucurí', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'San Zenón', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sandoná', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa Ana', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa Bárbara', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa Bárbara', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa Bárbara (Iscuandé)', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa Bárbara de Pinto', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa Catalina', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa Fé de Antioquia', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa Genoveva de Docorodó', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa Helena del Opón', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa Isabel', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa Lucía', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa Marta', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa María', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa María', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa Rosa', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa Rosa', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa Rosa de Cabal', 'departamento_id' => 66],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa Rosa de Osos', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa Rosa de Viterbo', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa Rosa del Sur', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa Rosalía', 'departamento_id' => 99],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santa Sofía', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santana', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santander de Quilichao', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santiago', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santiago', 'departamento_id' => 86],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santo Domingo', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santo Tomás', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santuario', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Santuario', 'departamento_id' => 66],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sapuyes', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Saravena', 'departamento_id' => 81],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sardinata', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sasaima', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sativanorte', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sativasur', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Segovia', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sesquilé', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sevilla', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Siachoque', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sibaté', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sibundoy', 'departamento_id' => 86],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Silos', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Silvania', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Silvia', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Simacota', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Simijaca', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Simití', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sincelejo', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sincé', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sipí', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sitionuevo', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Soacha', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Soatá', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Socha', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Socorro', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Socotá', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sogamoso', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Solano', 'departamento_id' => 18],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Soledad', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Solita', 'departamento_id' => 18],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Somondoco', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sonsón', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sopetrán', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Soplaviento', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sopó', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sora', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Soracá', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sotaquirá', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sotara (Paispamba)', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sotomayor (Los Andes)', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Suaita', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Suan', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Suaza', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Subachoque', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sucre', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sucre', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sucre', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Suesca', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Supatá', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Supía', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Suratá', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Susa', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Susacón', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sutamarchán', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sutatausa', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sutatenza', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Suárez', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Suárez', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sácama', 'departamento_id' => 85],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Sáchica', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tabio', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tadó', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Talaigua Nuevo', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tamalameque', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tame', 'departamento_id' => 81],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Taminango', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tangua', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Taraira', 'departamento_id' => 97],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tarazá', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tarqui', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tarso', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tasco', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tauramena', 'departamento_id' => 85],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tausa', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tello', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tena', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tenerife', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tenjo', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tenza', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Teorama', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Teruel', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tesalia', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tibacuy', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tibaná', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tibasosa', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tibirita', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tibú', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tierralta', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Timaná', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Timbiquí', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Timbío', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tinjacá', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tipacoque', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tiquisio (Puerto Rico)', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Titiribí', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Toca', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tocaima', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tocancipá', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Toguí', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Toledo', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Toledo', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tolú', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tolú Viejo', 'departamento_id' => 70],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tona', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Topagá', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Topaipí', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Toribío', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Toro', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tota', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Totoró', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Trinidad', 'departamento_id' => 85],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Trujillo', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tubará', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tuchín', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tulúa', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tumaco', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tunja', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tunungua', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Turbaco', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Turbaná', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Turbo', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Turmequé', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tuta', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Tutasá', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Támara', 'departamento_id' => 85],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Támesis', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Túquerres', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ubalá', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ubaque', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ubaté', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ulloa', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Une', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Unguía', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Unión Panamericana (ÁNIMAS)', 'departamento_id' => 27],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Uramita', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Uribe', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Uribia', 'departamento_id' => 44],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Urrao', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Urumita', 'departamento_id' => 44],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Usiacuri', 'departamento_id' => 8],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Valdivia', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Valencia', 'departamento_id' => 23],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Valle de San José', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Valle de San Juan', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Valle del Guamuez', 'departamento_id' => 86],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Valledupar', 'departamento_id' => 20],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Valparaiso', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Valparaiso', 'departamento_id' => 18],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Vegachí', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Venadillo', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Venecia', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Venecia (Ospina Pérez)', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ventaquemada', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Vergara', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Versalles', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Vetas', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Viani', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Vigía del Fuerte', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Vijes', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Villa Caro', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Villa Rica', 'departamento_id' => 19],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Villa de Leiva', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Villa del Rosario', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Villagarzón', 'departamento_id' => 86],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Villagómez', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Villahermosa', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Villamaría', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Villanueva', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Villanueva', 'departamento_id' => 44],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Villanueva', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Villanueva', 'departamento_id' => 85],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Villapinzón', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Villarrica', 'departamento_id' => 73],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Villavicencio', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Villavieja', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Villeta', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Viotá', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Viracachá', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Vista Hermosa', 'departamento_id' => 50],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Viterbo', 'departamento_id' => 17],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Vélez', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Yacopí', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Yacuanquer', 'departamento_id' => 52],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Yaguará', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Yalí', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Yarumal', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Yolombó', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Yondó (Casabe)', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Yopal', 'departamento_id' => 85],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Yotoco', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Yumbo', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Zambrano', 'departamento_id' => 13],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Zapatoca', 'departamento_id' => 68],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Zapayán (PUNTA DE PIEDRAS)', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Zaragoza', 'departamento_id' => 5],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Zarzal', 'departamento_id' => 76],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Zetaquirá', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Zipacón', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Zipaquirá', 'departamento_id' => 25],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Zona Bananera (PRADO - SEVILLA)', 'departamento_id' => 47],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Ábrego', 'departamento_id' => 54],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Íquira', 'departamento_id' => 41],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Úmbita', 'departamento_id' => 15],
			['status' => 0]);
		Municipio::updateOrCreate(
			['municipio' => 'Útica', 'departamento_id' => 25],
			['status' => 0]);
	}
}
