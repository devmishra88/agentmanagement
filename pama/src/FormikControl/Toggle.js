import React from "react";
import { Field, ErrorMessage } from "formik";
import { Switch, FormControlLabel } from "@mui/material";
import TextError from "./TextError";

function Toggle(props) {
  const { label, name, ...rest } = props;

  return (
    <FormControlLabel
      labelPlacement="start"
      label={label}
      sx={{
        display: `flex`,
        justifyContent: `space-between`,
        ml: 0,
        mt: 2,
      }}
      control={
        <Switch id={name} name={name} label={label} {...rest} color="primary" />
      }
    />
  );
}

export default Toggle;
