import React from "react";
import { Box, Fab, Typography } from "@mui/material";
import { AddIcon } from "../constants";

const AddFab = ({ ...props }) => {
  const { children, ...rest } = props;
  return (
    <Box
      sx={{
        position: "fixed",
        bottom: 16,
        left: "50%",
        transform: "translateX(-50%)",
      }}
    >
      <Fab variant="extended" color="primary" {...rest}>
        <AddIcon />
        <Box sx={{ marginLeft: 1 }}>
          <Typography variant="button">{children}</Typography>
        </Box>
      </Fab>
    </Box>
  );
};

export default AddFab;
