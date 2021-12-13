<?php

declare(strict_types=1);

namespace littler\Enums\Component;

class Form
{
	/**
	 * 输入框.
	 */
	public const Input = 'Input';

	/**
	 * 输入框组.
	 */
	public const InputGroup = 'InputGroup';

	/**
	 * 密码输入框.
	 */
	public const InputPassword = 'InputPassword';

	/**
	 * 搜索输入框.
	 */
	public const InputSearch = 'InputSearch';

	/**
	 * 多行文本输入框.
	 */
	public const InputTextArea = 'InputTextArea';

	/**
	 * 数字输入框.
	 */
	public const InputNumber = 'InputNumber';

	public const AutoComplete = 'AutoComplete';

	/**
	 * 选择.
	 */
	public const Select = 'Select';

	/**
	 * api调用选择.
	 */
	public const ApiSelect = 'ApiSelect';

	/**
	 * Tree选择.
	 */
	public const TreeSelect = 'TreeSelect';

	/**
	 * 开关.
	 */
	public const Switch = 'Switch';

	/**
	 * 单选组按钮.
	 */
	public const RadioButtonGroup = 'RadioButtonGroup';

	/**
	 * 单选组.
	 */
	public const RadioGroup = 'RadioGroup';

	/**
	 * 勾选.
	 */
	public const Checkbox = 'Checkbox';

	/**
	 * 勾选组.
	 */
	public const CheckboxGroup = 'CheckboxGroup';

	/**
	 * 及连选择.
	 */
	public const Cascader = 'Cascader';

	public const Slider = 'Slider';

	public const Rate = 'Rate';

	public const DatePicker = 'DatePicker';

	public const MonthPicker = 'MonthPicker';

	public const RangePicker = 'RangePicker';

	public const WeekPicker = 'WeekPicker';

	public const TimePicker = 'TimePicker';

	public const StrengthMeter = 'StrengthMeter';

	public const IconPicker = 'IconPicker';

	public const InputCountDown = 'InputCountDown';
}
