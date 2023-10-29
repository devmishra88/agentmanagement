import React from "react";

import { Container, Grid, Box } from "@mui/material";

import { AppHeader, Datacontainer } from "../components";

function ManageTemplate() {
  const datalist = [
    `data1`,
    `data2`,
    `data3`,
    `data4`,
    `data5`,
    `data6`,
    `data7`,
  ];

  return (
    <>
      <AppHeader>Manage Page Template</AppHeader>
      <Container maxWidth="lg">
        <Box mt={1} spacing={1}>
          {datalist.map((data) => (
            <Datacontainer key={data} />
          ))}
        </Box>
      </Container>
    </>
  );
}

export default ManageTemplate;
