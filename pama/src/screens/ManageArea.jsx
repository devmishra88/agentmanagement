import React from "react";
import { Container, Grid, Typography } from "@mui/material";
import { useDispatch } from "react-redux";
import { toggleLoader } from "../slices/CommonSlice";
import { AppHeader, CommonContainer, AddFab } from "../components";
import useSwitchRoute from "../hooks/useSwitchRoute";
import { useAreasData } from "../hooks/useAreaData";

function ManageArea() {
  const dispatch = useDispatch();
  const switchRoute = useSwitchRoute();

  const onSuccess = (data) => {
    // console.log(`Perform side effect after data fetching`, data);
  };

  const onError = (error) => {
    // console.log(`Perform side effect after encountering error`, error);
  };

  const { isLoading, isFetching, data, isError, error /*, refetch*/ } =
    useAreasData(onSuccess, onError);

  dispatch(toggleLoader({ loaderstatus: isLoading || isFetching }));

  if (isError) {
    return <h2>{error.message}</h2>;
  }

  return (
    <>
      <AppHeader>Manage Area</AppHeader>
      <Container maxWidth="lg">
        <Grid container mt={1} spacing={1}>
          {data?.data?.recordlist?.length > 0 ? (
            <Typography
              sx={{
                display: `block`,
                width: `100%`,
                textAlign: `center`,
                fontWeight: `bold`,
              }}
            >
              Total Area : {data?.data?.recordlist?.length}
            </Typography>
          ) : null}
          {data?.data?.recordlist?.map((area, index) => {
            return <CommonContainer key={area.id} {...area} />;
          })}
        </Grid>
      </Container>
      <AddFab onClick={() => switchRoute(`/area`, false)}>Add Area</AddFab>
    </>
  );
}

export default ManageArea;
