import React from "react";
import { TextField } from "@mui/material";

function PhoneInput(props) {
  const { label, name, maxLength, ...rest } = props;

  return (
    <TextField
      fullWidth
      id={name}
      name={name}
      label={label}
      variant="standard"
      margin="normal"
      InputProps={{
        type: "number",
      }}
      onInput={(e) => {
        e.target.value =
          e.target.value === "" || isNaN(e.target.value)
            ? ""
            : Math.max(0, parseInt(e.target.value))
                .toString()
                .slice(0, maxLength);
      }}
      {...rest}
    />
  );
}

export default PhoneInput;
