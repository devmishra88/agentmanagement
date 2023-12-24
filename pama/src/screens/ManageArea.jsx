import React, { useEffect } from "react";
import { Container, Grid, Typography } from "@mui/material";
import { useSelector, useDispatch } from "react-redux";
import { toggleLoader, confirmDelete } from "../slices/CommonSlice";
import { AppHeader, SingleAreaCard, AddFab } from "../components";
import useSwitchRoute from "../hooks/useSwitchRoute";
import { useAreasData } from "../hooks/useAreaData";

function ManageArea() {
  const dispatch = useDispatch();
  const { menuposition, menustatus, candelete, deletionrecordid } = useSelector((state) => state.common);
  const switchRoute = useSwitchRoute();

  const onSuccess = (data) => {
    // console.log(`Perform side effect after data fetching`, data);
  };

  const onError = (error) => {
    // console.log(`Perform side effect after encountering error`, error);
  };

  const { isLoading, isFetching, data, isError, error, deleteArea /*, refetch*/ } = useAreasData(onSuccess, onError);

  useEffect(() => {
    dispatch(toggleLoader({ loaderstatus: isLoading || isFetching }));
  }, [isLoading, isFetching]);

  useEffect(() => {
    if(candelete)
    {
      dispatch(toggleLoader({ loaderstatus: isLoading || isFetching }));
      deleteArea(deletionrecordid)
    }
  }, [deletionrecordid, candelete]);

  if (isError) {
    return <h2>{error?.message}</h2>;
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
            return (
              <SingleAreaCard
                key={area.id}
                {...area}
                deleteCallback={() =>
                  dispatch(
                    confirmDelete({
                      deletepopupstatus: true,
                      deletepopupposition: "bottom",
                      candelete:false,
                      deletionrecordid:area.id,
                    })
                  )
                }
              />
            );
          })}
        </Grid>
      </Container>
      <AddFab onClick={() => switchRoute(`/area`, false)}>Add Area</AddFab>
    </>
  );
}

export default ManageArea;
