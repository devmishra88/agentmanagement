import React from "react";

import { Container, Grid } from "@mui/material";

import { AppHeader, Datacontainer, AddFab } from "../components";
import useSwitchRoute from "../hooks/useSwitchRoute";

function ManageArea() {
  const switchRoute = useSwitchRoute();

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
      <AppHeader>Manage Area</AppHeader>
      <Container maxWidth="lg">
        <Grid container mt={1} spacing={1}>
          {datalist.map((data) => (
            <Datacontainer key={data} />
          ))}
        </Grid>
      </Container>
      <AddFab onClick={() => switchRoute(`/addarea`, false)}>Add Area</AddFab>
    </>
  );
}

export default ManageArea;
