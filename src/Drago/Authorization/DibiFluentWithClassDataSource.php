<?php

declare(strict_types=1);

namespace Drago\Authorization;

use Contributte\Datagrid\DataSource\DibiFluentDataSource;
use Contributte\Datagrid\DataSource\IDataSource;
use Contributte\Datagrid\Exception\DatagridDateTimeHelperException;
use Contributte\Datagrid\Filter\FilterDate;
use Contributte\Datagrid\Filter\FilterDateRange;
use Contributte\Datagrid\Filter\FilterMultiSelect;
use Contributte\Datagrid\Filter\FilterRange;
use Contributte\Datagrid\Filter\FilterSelect;
use Contributte\Datagrid\Filter\FilterText;
use Contributte\Datagrid\Utils\DateTimeHelper;
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
				$or[] = ["UPPER($column) LIKE UPPER(%~like~)", $word];
			}
		}
		if (count($or) > 1) {
			$this->dataSource->where('(%or)', $or);
		} else {
			$this->dataSource->where($or);
		}
	}


	protected function applyFilterMultiSelect(FilterMultiSelect $filter): void
	{
		$isFlags = array_key_exists('flags', $filter->getAttributes());

		$condition = $filter->getCondition();
		$values = $condition[$filter->getColumn()];

		if ((is_countable($values) ? count($values) : 0) > 1) {
			$value1 = array_shift($values);
			$length = count($values);
			$i = 1;

			if ($isFlags) {
				$this->dataSource->where('(BitAnd(%n, ?) > 0', strtoupper($filter->getColumn()), $value1);
			} else {
				$this->dataSource->where('(%n = ?', strtoupper($filter->getColumn()), $value1);
			}

			foreach ($values as $value) {
				if ($i === $length) {
					if ($isFlags) {
						$this->dataSource->__call('and', ['BitAnd(%n, ?) > 0)', strtoupper($filter->getColumn()), $value]);
					} else {
						$this->dataSource->__call('or', ['%n = ?)', strtoupper($filter->getColumn()), $value]);
					}
				} else {
					if ($isFlags) {
						$this->dataSource->__call('and', ['BitAnd(%n, ?) > 0', strtoupper($filter->getColumn()), $value]);
					} else {
						$this->dataSource->__call('or', ['%n = ?', strtoupper($filter->getColumn()), $value]);
					}
				}
				$i++;
			}
		} else {
			if ($isFlags) {
				$this->dataSource->where('BitAnd(%n, ?) > 0', strtoupper($filter->getColumn()), reset($values));
			} else {
				$this->dataSource->where('%n = ?', strtoupper($filter->getColumn()), reset($values));
			}
		}
	}


	protected function applyFilterSelect(FilterSelect $filter): void
	{
		$condition = array_change_key_case($filter->getCondition(), CASE_UPPER);
		$this->dataSource->where($condition);
	}


	protected function applyFilterDate(FilterDate $filter): void
	{
		$conditions = $filter->getCondition();

		try {
			$date = DateTimeHelper::tryConvertToDateTime($conditions[$filter->getColumn()], [$filter->getPhpFormat()]);
			$this->dataSource->where('TRUNC(%n) = DATE?', strtoupper($filter->getColumn()), $date->format('Y-m-d'));
		} catch (DatagridDateTimeHelperException) {
			// ignore the invalid filter value
		}
	}


	protected function applyFilterDateRange(FilterDateRange $filter): void
	{
		$conditions = $filter->getCondition();

		$valueFrom = $conditions[$filter->getColumn()]['from'];
		$valueTo = $conditions[$filter->getColumn()]['to'];

		if ($valueFrom) {
			try {
				$dateFrom = DateTimeHelper::tryConvertToDateTime($valueFrom, [$filter->getPhpFormat()]);
				$dateFrom->setTime(0, 0, 0);

				$this->dataSource->where('TRUNC(%n) >= DATE?', strtoupper($filter->getColumn()), $dateFrom);
			} catch (DatagridDateTimeHelperException) {
				// ignore the invalid filter value
			}
		}

		if ($valueTo) {
			try {
				$dateTo = DateTimeHelper::tryConvertToDateTime($valueTo, [$filter->getPhpFormat()]);
				$dateTo->setTime(23, 59, 59);

				$this->dataSource->where('TRUNC(%n) <= DATE?', strtoupper($filter->getColumn()), $dateTo);
			} catch (DatagridDateTimeHelperException) {
				// ignore the invalid filter value
			}
		}
	}


	protected function applyFilterRange(FilterRange $filter): void
	{
		$conditions = $filter->getCondition();

		$valueFrom = $conditions[$filter->getColumn()]['from'];
		$valueTo = $conditions[$filter->getColumn()]['to'];

		if (is_numeric($valueFrom)) {
			$this->dataSource->where('%n >= ?', strtoupper($filter->getColumn()), $valueFrom);
		}

		if (is_numeric($valueTo)) {
			$this->dataSource->where('%n <= ?', strtoupper($filter->getColumn()), $valueTo);
		}
	}


	public function sort(Sorting $sorting): IDataSource
	{
		$sorting = new Sorting(array_change_key_case($sorting->getSort(), CASE_UPPER), $sorting->getSortCallback());
		return parent::sort($sorting);
	}
}
