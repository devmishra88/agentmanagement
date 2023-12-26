import React from "react";
import Input from "./Input";
import PhoneInput from "./PhoneInput";
import Textarea from "./Textarea";
import Select from "./Select";
import RadioButton from "./RadioButton";
import CheckboxGroup from "./CheckboxGroup";
import DatePicker from "./DatePicker";
import Toggle from "./Toggle";

function FormikControl(props) {
  const { control, ...rest } = props;

  switch (control) {
    case "input":
      return <Input {...rest} />;
    case "number":
      return <Input {...rest} />;
    case "phone":
      return <PhoneInput {...rest} />;      
    case "textarea":
      return <Textarea {...rest} />;
    case "select":
      return <Select {...rest} />;
    case "radio":
      return <RadioButton {...rest} />;
    case "checkbox":
      return <CheckboxGroup {...rest} />;
    case "date":
      return <DatePicker {...rest} />;
    case "toggle":
      return <Toggle {...rest} />;
    default:
      return null;
  }
}

export default FormikControl;
