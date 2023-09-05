<?php

declare(strict_types=1);

namespace Drago\Authorization;

use Contributte\Datagrid\DataSource\DibiFluentDataSource;
use Contributte\Datagrid\DataSource\IDataSource;
use Contributte\Datagrid\Filter\FilterText;
use Contributte\Datagrid\Utils\Sorting;
use Dibi\Exception;
use Dibi\Fluent;


class FluentWithClassDataSource extends DibiFluentDataSource
{
	protected string $rowClass;


	public function __construct(Fluent $dataSource, string $primaryKey, string $rowClass)
	{
		parent::__construct($dataSource, $primaryKey);
		$this->rowClass = $rowClass;
	}


	/**
	 * @throws Exception
	 */
	public function getData(): array
	{
		return $this->data !== []
			? $this->data
			: $this->dataSource->execute()->setRowClass($this->rowClass)->fetchAll();
	}


	/**
	 * @throws Exception
	 */
	public function limit(int $offset, int $limit): IDataSource
	{
		$this->dataSource->limit($limit)->offset($offset);
		$this->data = $this->dataSource->execute()->setRowClass($this->rowClass)->fetchAll();
		return $this;
	}


	public function filterOne(array $condition): IDataSource
	{
		$condition[0] = strtoupper($condition[0]);
		$this->dataSource->where($condition)->limit(1);
		return $this;
	}


	protected function applyFilterText(FilterText $filter): void
	{
		$condition = $filter->getCondition();
		$or = [];
		foreach ($condition as $column => $value) {
			if ($filter->isExactSearch()) {
				$this->dataSource->where("$column = %s", $value);
				continue;
			}
			$words = $filter->hasSplitWordsSearch() === false ? [$value] : explode(' ', $value);
			foreach ($words as $word) {
				$word = strtoupper($word);
				$or[] = ["UPPER($column) LIKE %~like~", $word];
			}
		}
		if (count($or) > 1) {
			$this->dataSource->where('(%or)', $or);
		} else {
			$this->dataSource->where($or);
		}
	}


	public function sort(Sorting $sorting): IDataSource
	{
		$sorting = new Sorting(array_change_key_case($sorting->getSort(), CASE_UPPER), $sorting->getSortCallback());
		return parent::sort($sorting);
	}
}
