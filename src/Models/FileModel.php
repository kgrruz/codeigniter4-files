<?php namespace Tatter\Files\Models;

use Tatter\Permits\Model;

class FileModel extends Model
{
	use \Tatter\Audits\Traits\AuditsTrait;

	protected $table      = 'files';
	protected $primaryKey = 'id';
	protected $returnType = 'Tatter\Files\Entities\File';

	protected $useTimestamps  = true;
	protected $useSoftDeletes = true;
	protected $skipValidation = false;

	protected $allowedFields = [
		'filename', 'localname', 'clientname',
		'type', 'size', 'thumbnail',
	];

	protected $validationRules = [
		'filename' => 'required|max_length[255]',
		'size'     => 'permit_empty|is_natural]',
	];

	// Audits
	protected $afterInsert = ['auditInsert'];
	protected $afterUpdate = ['auditUpdate'];
	protected $afterDelete = ['auditDelete'];

	// Permits
	protected $mode       = 04660;
	protected $usersPivot = 'files_users';
	protected $pivotKey   = 'file_id';

	// Associate a file with a user
	public function addToUser(int $fileId, int $userId)
	{
		$row = [
			'file_id' => (int)$fileId,
			'user_id' => (int)$userId,
		];

		return $this->db->table('files_users')->insert($row);
	}

	// Returns an array of all a user's files
	public function getForUser(int $userId): array
	{
		return $this->builder()
			->select('files.*')
			->join('files_users', 'files_users.file_id = files.id', 'left')
			->where('user_id', $userId)
			->where('deleted_at IS NULL')
			->get()->getResult($this->returnType);
	}
}
