import React from "react";
import { TextField } from "@mui/material";

function Input(props) {
  const { label, name, ...rest } = props;

  return (
    <TextField
      fullWidth
      id={name}
      name={name}
      label={label}
      variant="standard"
      margin="normal"
      {...rest}
    />
  );
}

export default Input;
