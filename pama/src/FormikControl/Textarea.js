import React from "react";
import { TextField } from "@mui/material";
import {Textarea as MUITextarea} from '@mui/joy';
// import { Field, ErrorMessage } from "formik";
// import TextError from "./TextError";

function Textarea(props) {
  const { label, name, ...rest } = props;
  return (
    <MUITextarea
      // variant="standard"
      // margin="normal"
      minRows={2}
      id={name}
      name={name}
      label={label}
      {...rest}
    />
  );
}

export default Textarea;